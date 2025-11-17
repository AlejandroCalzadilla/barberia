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
        $tipo = $request->input('tipo');

        $pagos = Pago::query()
            ->with(['reserva.cliente.user:id,name', 'reserva.barbero.user:id,name', 'detallePagoProductos.producto'])
            ->when($reservaId, fn($q) => $q->where('id_reserva', $reservaId))
            ->when(is_string($estado) && $estado !== '', fn($q) => $q->where('estado', $estado))
            ->when(is_string($tipo) && $tipo !== '', fn($q) => $q->where('tipo', $tipo))
            ->when(is_string($fecha) && $fecha !== '', fn($q) => $q->whereDate('fecha_pago', $fecha))
            ->orderByDesc('id_pago')
            ->paginate(10)
            ->withQueryString();

        $reservas = Reserva::with(['cliente.user:id,name','barbero.user:id,name'])
            ->orderByDesc('id_reserva')
            ->get(['id_reserva','id_cliente','id_barbero']);

        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'reservas' => $reservas,
            'filters' => [
                'reserva' => $reservaId,
                'estado' => $estado,
                'tipo' => $tipo,
                'fecha' => $fecha,
            ],
            'metodosPago' => ['efectivo', 'tarjeta', 'transferencia', 'otro'],
            'tiposPago' => ['anticipo', 'pago_parcial', 'pago_completo', 'producto'],
            'estadosPago' => ['pendiente', 'completado', 'rechazado', 'reembolsado'],
        ]);
    }

    public function create()
    {
        $reservas = Reserva::with(['cliente.user:id,name', 'barbero.user:id,name', 'servicio'])
            ->whereIn('estado', ['confirmada', 'en_proceso'])
            ->orderByDesc('id_reserva')
            ->get(['id_reserva', 'id_cliente', 'id_barbero', 'id_servicio', 'total', 'monto_anticipo']);

        $productos = Producto::where('estado', 'activo')
            ->where('stock_actual', '>', 0)
            ->orderBy('nombre')
            ->get(['id_producto', 'nombre', 'precio_venta', 'stock_actual']);

        return Inertia::render('Payments/Create', [
            'reservas' => $reservas,
            'productos' => $productos,
            'metodosPago' => ['efectivo', 'tarjeta', 'transferencia', 'otro'],
            'tiposPago' => ['anticipo', 'pago_parcial', 'pago_completo', 'producto'],
            'hoy' => now()->format('Y-m-d'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_reserva' => 'required|exists:bookings,id_reserva',
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,otro',
            'estado' => 'required|in:pendiente,completado,rechazado,reembolsado',
            'tipo' => 'required|in:anticipo,pago_parcial,pago_completo,producto',
            'notas' => 'nullable|string',
            'productos' => 'nullable|array',
            'productos.*.id_producto' => 'required_with:productos|exists:products,id_producto',
            'productos.*.cantidad' => 'required_with:productos|integer|min:1',
            'productos.*.precio' => 'required_with:productos|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Verificar disponibilidad de stock para productos
            if (!empty($validated['productos'])) {
                foreach ($validated['productos'] as $producto) {
                    $productoDB = Producto::find($producto['id_producto']);
                    if ($productoDB->stock_actual < $producto['cantidad']) {
                        return back()->with('error', 'No hay suficiente stock para el producto: ' . $productoDB->nombre);
                    }
                }
            }

            // Crear el pago
            $pago = Pago::create($validated);

            // Si hay productos, crear los detalles del pago
            if (!empty($validated['productos'])) {
                foreach ($validated['productos'] as $producto) {
                    $pago->detallePagoProductos()->create([
                        'id_producto' => $producto['id_producto'],
                        'cantidad' => $producto['cantidad'],
                        'precio_unitario' => $producto['precio'],
                        'subtotal' => $producto['cantidad'] * $producto['precio'],
                    ]);

                    // Actualizar el stock del producto
                    $productoModel = Producto::find($producto['id_producto']);
                    $productoModel->decrement('stock_actual', $producto['cantidad']);
                }
            }

            // Actualizar el estado de la reserva según el tipo de pago
            $reserva = Reserva::find($validated['id_reserva']);
            if ($validated['estado'] === 'completado') {
                if ($validated['tipo'] === 'pago_completo') {
                    $reserva->estado = 'completada';
                } elseif ($validated['tipo'] === 'anticipo') {
                    $reserva->estado = 'confirmada';
                }
                $reserva->save();
            }

            DB::commit();

            return redirect()
                ->route('pagos.index')
                ->with('success', 'Pago registrado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function edit(Pago $pago)
    {
        $reservas = Reserva::with(['cliente.user:id,name', 'barbero.user:id,name'])
            ->orderByDesc('id_reserva')
            ->get(['id_reserva', 'id_cliente', 'id_barbero']);

        $productos = Producto::orderBy('nombre')
            ->get(['id_producto', 'nombre', 'precio_venta']);

        return Inertia::render('Pagos/Edit', [
            'pago' => $pago->load(['reserva.cliente.user:id,name', 'reserva.barbero.user:id,name', 'detalles.producto:id_producto,nombre,precio_venta']),
            'reservas' => $reservas,
            'productos' => $productos,
            'metodosPago' => ['efectivo', 'tarjeta', 'transferencia', 'otro'],
            'tiposPago' => ['anticipo', 'pago_parcial', 'pago_completo', 'producto'],
            'estadosPago' => ['pendiente', 'completado', 'rechazado', 'reembolsado'],
        ]);
    }

    public function update(Request $request, Pago $pago)
    {
        $validated = $request->validate([
            'id_reserva' => 'required|exists:bookings,id_reserva',
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,otro',
            'estado' => 'required|in:pendiente,completado,rechazado,reembolsado',
            'tipo' => 'required|in:anticipo,pago_parcial,pago_completo,producto',
            'notas' => 'nullable|string',
            'productos' => 'nullable|array',
            'productos.*.id_producto' => 'required_with:productos|exists:products,id_producto',
            'productos.*.cantidad' => 'required_with:productos|integer|min:1',
            'productos.*.precio' => 'required_with:productos|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Verificar disponibilidad de stock para productos (solo si se están modificando)
            if ($validated['tipo'] === 'producto' && !empty($validated['productos'])) {
                // Primero revertir el stock de los productos actuales
                foreach ($pago->detallePagoProductos as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    $producto->increment('stock_actual', $detalle->cantidad);
                }
                
                // Verificar stock para los nuevos productos
                foreach ($validated['productos'] as $producto) {
                    $productoDB = Producto::find($producto['id_producto']);
                    if ($productoDB->stock_actual < $producto['cantidad']) {
                        throw new \Exception('No hay suficiente stock para el producto: ' . $productoDB->nombre);
                    }
                }
                
                // Eliminar los detalles antiguos
                $pago->detallePagoProductos()->delete();
            }

            // Actualizar el pago
            $pago->update($validated);

            // Si es un pago de productos, crear los nuevos detalles
            if ($validated['tipo'] === 'producto' && !empty($validated['productos'])) {
                foreach ($validated['productos'] as $producto) {
                    $pago->detallePagoProductos()->create([
                        'id_producto' => $producto['id_producto'],
                        'cantidad' => $producto['cantidad'],
                        'precio_unitario' => $producto['precio'],
                        'subtotal' => $producto['cantidad'] * $producto['precio'],
                    ]);

                    // Actualizar el stock del producto
                    $productoModel = Producto::find($producto['id_producto']);
                    $productoModel->decrement('stock_actual', $producto['cantidad']);
                }
            }

            // Actualizar el estado de la reserva según el tipo de pago
            $reserva = $pago->reserva;
            if ($validated['estado'] === 'completado') {
                if ($validated['tipo'] === 'pago_completo') {
                    $reserva->estado = 'completada';
                } elseif ($validated['tipo'] === 'anticipo') {
                    $reserva->estado = 'confirmada';
                }
                $reserva->save();
            }

            DB::commit();

            return redirect()
                ->route('pagos.index')
                ->with('success', 'Pago actualizado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el pago: ' . $e->getMessage());
        }
    }

    public function destroy(Pago $pago)
    {
        DB::beginTransaction();
        
        try {
            // Revertir el stock de los productos si es necesario
            if ($pago->estado === 'completado' && $pago->tipo === 'producto') {
                foreach ($pago->detallePagoProductos as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    if ($producto) {
                        $producto->increment('stock_actual', $detalle->cantidad);
                    }
                }
            }
            
            // Eliminar detalles de pago de productos
            $pago->detallePagoProductos()->delete();
            
            // Actualizar el estado de la reserva si es necesario
            if ($pago->reserva && $pago->estado === 'completado') {
                // Verificar si hay otros pagos completados para esta reserva
                $otrosPagos = Pago::where('id_reserva', $pago->id_reserva)
                    ->where('id_pago', '!=', $pago->id_pago)
                    ->where('estado', 'completado')
                    ->exists();
                
                if (!$otrosPagos) {
                    $pago->reserva->estado = 'pendiente_pago';
                    $pago->reserva->save();
                }
            }
            
            // Eliminar el pago
            $pago->delete();
            
            DB::commit();

            return redirect()
                ->route('pagos.index')
                ->with('success', 'Pago eliminado correctamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el pago: ' . $e->getMessage());
        }
    }
     
}
