<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Barbero;
use App\Models\User;

class BarberoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:barberos.view')->only(['index']);
        $this->middleware('permission:barberos.create')->only(['create','store']);
        $this->middleware('permission:barberos.update')->only(['edit','update']);
        $this->middleware('permission:barberos.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $q = $request->string('q');

        $barberos = Barbero::query()
            ->with(['user:id,name,email'])
            ->when($q, function ($query) use ($q) {
                $query->whereHas('user', function ($uq) use ($q) {
                    $uq->where('name', 'ilike', "%{$q}%")
                       ->orWhere('email', 'ilike', "%{$q}%");
                });
            })
            ->orderByDesc('id_barbero')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Barberos/Index', [
            'barberos' => $barberos,
            'filters' => ['q' => $q],
        ]);
    }

    public function create()
    {
        $usuarios = User::where('tipo_usuario', 'barbero')->orderBy('name')->get(['id','name','email']);
        return Inertia::render('Barberos/Create', [
            'usuarios' => $usuarios,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_usuario' => ['required', 'unique:barbero,id_usuario', 'exists:users,id'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'foto_perfil' => ['nullable', 'string', 'max:255'],
            'calificacion_promedio' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'estado' => ['nullable', 'in:disponible,ocupado,ausente'],
        ]);

        Barbero::create($data);
        return redirect()->route('barberos.index');
    }

    public function edit(Barbero $barbero)
    {
        $usuarios = User::where('tipo_usuario', 'barbero')->orderBy('name')->get(['id','name','email']);
        return Inertia::render('Barberos/Edit', [
            'barbero' => $barbero->load('user:id,name,email'),
            'usuarios' => $usuarios,
        ]);
    }

    public function update(Request $request, Barbero $barbero)
    {
        $data = $request->validate([
            'id_usuario' => ['required', 'exists:users,id', 'unique:barbero,id_usuario,' . $barbero->id_barbero . ',id_barbero'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'foto_perfil' => ['nullable', 'string', 'max:255'],
            'calificacion_promedio' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'estado' => ['nullable', 'in:disponible,ocupado,ausente'],
        ]);

        $barbero->update($data);
        return redirect()->route('barberos.index');
    }

    public function destroy(Barbero $barbero)
    {
        $barbero->delete();
        return redirect()->route('barberos.index');
    }
}
