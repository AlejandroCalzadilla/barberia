<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\Barbero;
use App\Models\Servicio;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reservas.view')->only(['index']);
        $this->middleware('permission:reservas.create')->only(['create','store']);
        $this->middleware('permission:reservas.update')->only(['edit','update']);
        $this->middleware('permission:reservas.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $clienteId = $request->integer('cliente');
        $barberoId = $request->integer('barbero');
        $estado = $request->input('estado');
        $fecha = $request->input('fecha');

        $reservas = Reserva::query()
            ->with(['cliente.user:id,name', 'barbero.user:id,name', 'servicio:id_servicio,nombre'])
            ->when($clienteId, fn($q) => $q->where('id_cliente', $clienteId))
            ->when($barberoId, fn($q) => $q->where('id_barbero', $barberoId))
            ->when(is_string($estado) && $estado !== '', fn($q) => $q->where('estado', $estado))
            ->when(is_string($fecha) && $fecha !== '', fn($q) => $q->whereDate('fecha_reserva', $fecha))
            ->orderByDesc('id_reserva')
            ->paginate(10)
            ->withQueryString();

        $clientes = Cliente::with('user:id,name')->get(['id_cliente','id_usuario']);
        $barberos = Barbero::with('user:id,name')->get(['id_barbero','id_usuario']);
        $servicios = Servicio::orderBy('nombre')->get(['id_servicio','nombre']);

        return Inertia::render('Reservas/Index', [
            'reservas' => $reservas,
            'clientes' => $clientes,
            'barberos' => $barberos,
            'servicios' => $servicios,
            'filters' => [
                'cliente' => $clienteId,
                'barbero' => $barberoId,
                'estado' => $estado,
                'fecha' => $fecha,
            ],
        ]);
    }

    public function create()
    {
        $clientes = Cliente::with('user:id,name')->get(['id_cliente','id_usuario']);
        $barberos = Barbero::with('user:id,name')->get(['id_barbero','id_usuario']);
        $servicios = Servicio::orderBy('nombre')->get(['id_servicio','nombre','precio','duracion_minutos']);

        return Inertia::render('Bookings/Create', [
            'clientes' => $clientes,
            'barberos' => $barberos,
            'servicios' => $servicios,
            'hoy' => now()->format('Y-m-d'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_cliente' => 'required|exists:clients,id_cliente',
            'id_barbero' => 'required|exists:barbers,id_barbero',
            'id_servicio' => 'required|exists:services,id_servicio',
            'fecha_reserva' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'estado' => 'required|in:pendiente,confirmada,en_proceso,completada,cancelada,no_asistio',
            'notas' => 'nullable|string',
            'monto_anticipo' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            // Calcular hora de finalización basada en la duración del servicio
            $servicio = Servicio::findOrFail($validated['id_servicio']);
            $horaInicio = Carbon::parse($validated['hora_inicio']);
            $horaFin = $horaInicio->copy()->addMinutes($servicio->duracion_minutos);

            $validated['hora_fin'] = $horaFin->format('H:i');
            $validated['total'] = $servicio->precio;

            // Verificar disponibilidad del barbero
            $existeReserva = Reserva::where('id_barbero', $validated['id_barbero'])
                ->where('fecha_reserva', $validated['fecha_reserva'])
                ->where(function ($query) use ($horaInicio, $horaFin) {
                    $query->whereBetween('hora_inicio', [$horaInicio->format('H:i'), $horaFin->format('H:i')])
                        ->orWhereBetween('hora_fin', [$horaInicio->format('H:i'), $horaFin->format('H:i')])
                        ->orWhere(function ($q) use ($horaInicio, $horaFin) {
                            $q->where('hora_inicio', '<=', $horaInicio->format('H:i'))
                                ->where('hora_fin', '>=', $horaFin->format('H:i'));
                        });
                })
                ->whereNotIn('estado', ['cancelada', 'no_asistio'])
                ->exists();

            if ($existeReserva) {
                return back()->with('error', 'El barbero ya tiene una reserva en ese horario');
            }

            $reserva = Reserva::create($validated);

            // Crear pago de anticipo si existe
            if (!empty($validated['monto_anticipo']) && $validated['monto_anticipo'] > 0) {
                $reserva->pagos()->create([
                    'monto' => $validated['monto_anticipo'],
                    'fecha_pago' => now(),
                    'metodo_pago' => 'efectivo',
                    'estado' => 'completado',
                    'tipo' => 'anticipo',
                ]);
            }

            DB::commit();

            return redirect()
                ->route('reservas.index')
                ->with('success', 'Reserva creada correctamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la reserva: ' . $e->getMessage());
        }
    }

    public function edit(Reserva $reserva)
    {
        $reserva->load(['cliente.user', 'barbero.user', 'servicio', 'pagos']);
        $clientes = Cliente::with('user:id,name')->get(['id_cliente','id_usuario']);
        $barberos = Barbero::with('user:id,name')->get(['id_barbero','id_usuario']);
        $servicios = Servicio::orderBy('nombre')->get(['id_servicio','nombre','precio','duracion_minutos']);

        return Inertia::render('Bookings/Edit', [
            'reserva' => $reserva,
            'clientes' => $clientes,
            'barberos' => $barberos,
            'servicios' => $servicios,
            'hoy' => now()->format('Y-m-d'),
        ]);
    }

    public function update(Request $request, Reserva $reserva)
    {
        $data = $request->validate([
            'id_cliente' => ['required', 'exists:cliente,id_cliente'],
            'id_barbero' => ['required', 'exists:barbero,id_barbero'],
            'id_servicio' => ['required', 'exists:servicio,id_servicio'],
            'fecha_reserva' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'notas' => ['nullable', 'string'],
            'precio_servicio' => ['required', 'numeric', 'min:0'],
            'monto_anticipo' => ['nullable', 'numeric', 'min:0'],
            'porcentaje_anticipo' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'estado' => ['nullable', 'in:pendiente_pago,confirmada,en_proceso,completada,cancelada,no_asistio'],
        ]);

        $reserva->update($data);
        return redirect()->route('reservas.index');
    }

    public function destroy(Reserva $reserva)
    {
        DB::beginTransaction();
        
        try {
            if ($reserva->estado === 'completada') {
                return back()->with('error', 'No se puede eliminar una reserva completada');
            }

            // Eliminar pagos asociados
            $reserva->pagos()->delete();
            
            // Eliminar la reserva
            $reserva->delete();
            
            DB::commit();

            return redirect()
                ->route('reservas.index')
                ->with('success', 'Reserva eliminada correctamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la reserva: ' . $e->getMessage());
        }
    }
}
