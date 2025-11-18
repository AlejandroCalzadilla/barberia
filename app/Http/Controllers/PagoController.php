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
        $this->middleware('permission:pagos.view')->only(['index']);
        $this->middleware('permission:pagos.create')->only(['create','store']);
        $this->middleware('permission:pagos.update')->only(['edit','update']);
        $this->middleware('permission:pagos.delete')->only(['destroy']);
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

    public function create()
    {
        $reservas = Reserva::with(['cliente.user:id,name', 'barbero.user:id,name'])
            ->orderByDesc('id_reserva')
            ->get(['id_reserva', 'id_cliente', 'id_barbero']);

        return Inertia::render('Pagos/Create', [
            'reservas' => $reservas,
            'metodosPago' => ['efectivo', 'tarjeta', 'transferencia', 'otro'],
            'hoy' => now()->format('Y-m-d'),
        ]);
    }

    public function store(Request $request)
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
            $pago = Pago::create($validated);

            return redirect()
                ->route('pagos.index')
                ->with('success', 'Pago registrado correctamente');

        } catch (\Exception $e) {
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
     
}
