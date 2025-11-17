<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Horario;
use App\Models\Barbero;

class HorarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:horarios.view')->only(['index']);
        $this->middleware('permission:horarios.create')->only(['create','store']);
        $this->middleware('permission:horarios.update')->only(['edit','update']);
        $this->middleware('permission:horarios.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $barberoId = $request->integer('barbero');

        $horarios = Horario::query()
            ->with(['barbero.user:id,name'])
            ->when($barberoId, fn($q) => $q->where('id_barbero', $barberoId))
            ->orderByDesc('id_horario')
            ->paginate(10)
            ->withQueryString();

        $barberos = Barbero::with('user:id,name')->get(['id_barbero','id_usuario']);

        return Inertia::render('Horarios/Index', [
            'horarios' => $horarios,
            'barberos' => $barberos,
            'filters' => [
                'barbero' => $barberoId,
            ],
        ]);
    }

    public function create()
    {
        $barberos = Barbero::with('user:id,name')->get(['id_barbero','id_usuario']);
        return Inertia::render('Horarios/Create', [
            'barberos' => $barberos,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_barbero' => ['required', 'exists:barbero,id_barbero'],
            'dia_semana' => ['required', 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'estado' => ['nullable', 'in:activo,inactivo'],
        ]);

        Horario::create($data);
        return redirect()->route('horarios.index');
    }

    public function edit(Horario $horario)
    {
        $barberos = Barbero::with('user:id,name')->get(['id_barbero','id_usuario']);
        return Inertia::render('Horarios/Edit', [
            'horario' => $horario->load('barbero.user:id,name'),
            'barberos' => $barberos,
        ]);
    }

    public function update(Request $request, Horario $horario)
    {
        $data = $request->validate([
            'id_barbero' => ['required', 'exists:barbero,id_barbero'],
            'dia_semana' => ['required', 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'estado' => ['nullable', 'in:activo,inactivo'],
        ]);

        $horario->update($data);
        return redirect()->route('horarios.index');
    }

    public function destroy(Horario $horario)
    {
        $horario->delete();
        return redirect()->route('horarios.index');
    }
}
