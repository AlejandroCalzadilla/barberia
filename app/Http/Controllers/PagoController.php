<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Producto;
use App\Models\DetallePagoProducto;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagoController extends Controller
{
    public function __construct()
    {
       /*  $this->middleware('permission:pagos.view')->only(['index']);
        $this->middleware('permission:pagos.create')->only(['create','store']);
        $this->middleware('permission:pagos.update')->only(['edit','update']);
        $this->middleware('permission:pagos.delete')->only(['destroy']); */
    }

    public function index(Request $request)
    {
        $reservaId = $request->integer('reserva');
        $estado = $request->input('estado');
        $fecha = $request->input('fecha');

        $pagos = Pago::query()
            ->with(['reserva.cliente.user:id,name', 'reserva.barbero.user:id,name', 'reserva.servicio:id_servicio,nombre'])
            ->when($reservaId, fn($q) => $q->where('id_reserva', $reservaId))
            ->when(is_string($estado) && $estado !== '', fn($q) => $q->where('estado', $estado))
            ->when(is_string($fecha) && $fecha !== '', fn($q) => $q->whereDate('fecha_pago', $fecha))
            ->orderByDesc('id_pago')
            ->paginate(10)
            ->withQueryString();

        $reservas = Reserva::with(['cliente.user:id,name','barbero.user:id,name'])
            ->orderByDesc('id_reserva')
            ->get(['id_reserva','id_cliente','id_barbero']);

        //dd($pagos);     
        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'reservas' => $reservas,
            'filters' => [
                'reserva' => $reservaId,
                'estado' => $estado,
                'fecha' => $fecha,
            ],

            'metodosPago' => ['efectivo', 'tarjeta', 'transferencia', 'otro'],
            'estadosPago' => ['pendiente', 'pagado', 'cancelado', 'reembolsado'],
        ]);
    }

    public function create(Request $request)
    {
        // Datos de la reserva desde el catálogo (query params)
        $datosReserva = [
            'id_servicio' => $request->integer('id_servicio'),
            'id_barbero' => $request->integer('id_barbero'),
            'fecha_reserva' => $request->input('fecha_reserva'),
            'hora_inicio' => $request->input('hora_inicio'),
        ];

        // Si vienen datos de reserva desde el catálogo, cargar el servicio
        $servicio = null;
        $barbero = null;
        if ($datosReserva['id_servicio']) {
            $servicio = \App\Models\Servicio::find($datosReserva['id_servicio']);
            $barbero = \App\Models\Barbero::with('user:id,name')->find($datosReserva['id_barbero']);
        }

        $reservas = Reserva::with(['cliente.user:id,name', 'barbero.user:id,name'])
            ->orderByDesc('id_reserva')
            ->get(['id_reserva', 'id_cliente', 'id_barbero']);

        return Inertia::render('Pagos/Create', [
            'reservas' => $reservas,
            'metodosPago' => ['efectivo', 'tarjeta', 'transferencia', 'otro'],
            'hoy' => now()->format('Y-m-d'),
            'datosReserva' => $datosReserva,
            'servicio' => $servicio,
            'barbero' => $barbero,
        ]);
    }

    /**
     * Mostrar vista de pago por QR para reserva desde catálogo
     */
    public function pagarReserva(Request $request)
    {
        $datosReserva = [
            'id_servicio' => $request->integer('id_servicio'),
            'id_barbero' => $request->integer('id_barbero'),
            'fecha_reserva' => $request->input('fecha_reserva'),
            'hora_inicio' => $request->input('hora_inicio'),
        ];

        // Validar que vengan todos los datos necesarios
        if (!$datosReserva['id_servicio'] || !$datosReserva['id_barbero'] || 
            !$datosReserva['fecha_reserva'] || !$datosReserva['hora_inicio']) {
            return redirect()->route('servicios.catalogo')
                ->with('error', 'Faltan datos para procesar el pago');
        }

        // Cargar información completa
        $servicio = \App\Models\Servicio::findOrFail($datosReserva['id_servicio']);
        $barbero = \App\Models\Barbero::with('user:id,name')->findOrFail($datosReserva['id_barbero']);

        // Calcular hora de fin
        $horaInicio = Carbon::parse($datosReserva['hora_inicio']);
        $horaFin = $horaInicio->copy()->addMinutes($servicio->duracion_minutos);

        return Inertia::render('Pagos/PagarReserva', [
            'servicio' => $servicio,
            'barbero' => $barbero,
            'fecha_reserva' => $datosReserva['fecha_reserva'],
            'hora_inicio' => $datosReserva['hora_inicio'],
            'hora_fin' => $horaFin->format('H:i'),
            'monto_total' => $servicio->precio,
            'datosReserva' => $datosReserva,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_reserva' => 'nullable|exists:reserva,id_reserva',
            'monto_total' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,otro',
            'tipo_pago' => 'required|in:anticipo,pago_parcial,pago_completo',
            'estado' => 'required|in:pendiente,pagado,cancelado,reembolsado',
            'notas' => 'nullable|string',
            // Datos para crear la reserva si no existe
            'id_servicio' => 'nullable|exists:servicio,id_servicio',
            'id_barbero' => 'nullable|exists:barbero,id_barbero',
            'fecha_reserva' => 'nullable|date',
            'hora_inicio' => 'nullable|date_format:H:i',
        ]);

        DB::beginTransaction();

        try {
            $idReserva = $validated['id_reserva'];

            // Si no hay id_reserva, crear la reserva primero
            if (!$idReserva && $validated['id_servicio']) {
                $user = auth()->user();
                $cliente = $user->cliente;

                if (!$cliente) {
                    return back()->with('error', 'No tienes un perfil de cliente asociado');
                }

                $servicio = \App\Models\Servicio::findOrFail($validated['id_servicio']);
                $horaInicio = Carbon::parse($validated['hora_inicio']);
                $horaFin = $horaInicio->copy()->addMinutes($servicio->duracion_minutos);

                $reserva = Reserva::create([
                    'id_cliente' => $cliente->id_cliente,
                    'id_barbero' => $validated['id_barbero'],
                    'id_servicio' => $validated['id_servicio'],
                    'fecha_reserva' => $validated['fecha_reserva'],
                    'hora_inicio' => $validated['hora_inicio'],
                    'hora_fin' => $horaFin->format('H:i'),
                    'precio_servicio' => $servicio->precio,
                    'total' => $servicio->precio,
                    'estado' => $validated['estado'] === 'pagado' ? 'confirmada' : 'pendiente_pago',
                ]);

                $idReserva = $reserva->id_reserva;
            }

            $pago = Pago::create([
                'id_reserva' => $idReserva,
                'monto_total' => $validated['monto_total'],
                'fecha_pago' => $validated['fecha_pago'],
                'metodo_pago' => $validated['metodo_pago'],
                'tipo_pago' => $validated['tipo_pago'],
                'estado' => $validated['estado'],
                'notas' => $validated['notas'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('servicios.catalogo')
                ->with('success', 'Reserva y pago registrados correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function show(Pago $pago)
    {
        return Inertia::render('Pagos/Show', [
            'pago' => $pago->load(['reserva.cliente.user:id,name', 'reserva.barbero.user:id,name', 'reserva.servicio:id_servicio,nombre']),
        ]);
    }

    public function edit(Pago $pago)
    {
        $reservas = Reserva::with(['cliente.user:id,name', 'barbero.user:id,name'])
            ->orderByDesc('id_reserva')
            ->get(['id_reserva', 'id_cliente', 'id_barbero']);

        return Inertia::render('Pagos/Edit', [
            'pago' => $pago->load(['reserva.cliente.user:id,name', 'reserva.barbero.user:id,name']),
            'reservas' => $reservas,
            'metodosPago' => ['efectivo', 'tarjeta', 'transferencia', 'otro'],
            'tiposPago' => ['anticipo', 'pago_parcial', 'pago_completo'],
            'estadosPago' => ['pendiente', 'pagado', 'cancelado', 'reembolsado'],
        ]);
    }

    public function update(Request $request, Pago $pago)
    {
        $validated = $request->validate([
            'id_reserva' => 'required|exists:reserva,id_reserva',
            'monto_total' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,otro',
            'tipo_pago' => 'required|in:anticipo,pago_parcial,pago_completo',
            'estado' => 'required|in:pendiente,pagado,cancelado,reembolsado',
            'notas' => 'nullable|string',
        ]);

        try {
            $pago->update($validated);

            return redirect()
                ->route('pagos.index')
                ->with('success', 'Pago actualizado correctamente');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el pago: ' . $e->getMessage());
        }
    }

    public function destroy(Pago $pago)
    {
        try {
            $pago->delete();

            return redirect()
                ->route('pagos.index')
                ->with('success', 'Pago eliminado correctamente');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Obtener el estado actual de un pago
     */
    public function obtenerEstado($id)
    {
        try {
            $pago = Pago::with(['reserva:id_reserva,estado'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'pago' => [
                    'id_pago' => $pago->id_pago,
                    'estado' => $pago->estado,
                    'fecha_pago' => $pago->fecha_pago,
                    'monto_total' => $pago->monto_total,
                    'reserva_estado' => $pago->reserva->estado ?? null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado del pago: ' . $e->getMessage()
            ], 500);
        }
    }
     
}
