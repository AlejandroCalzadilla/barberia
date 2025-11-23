<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\Barbero;
use App\Models\Servicio;
use App\Models\Cliente;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function index()
    {
        $fechaInicio = now()->subMonth();
        $fechaFin = now();
        $año = now()->year;
        $mes = now()->month;

        // Datos básicos
        $pagosData = Pago::where('estado', 'pagado')
            ->orderBy('fecha_pago')
            ->get(['id_pago', 'monto_total', 'fecha_pago', 'estado']);
          
        // dd($pagosData);       

        $reservasData = Reserva::orderBy('id_reserva')
            ->get(['id_reserva', 'estado']);

        // Ingresos mensuales
        $ingresosMensuales = Pago::query()
            ->join('reserva', 'pago.id_reserva', '=', 'reserva.id_reserva')
            ->whereYear('reserva.created_at', $año)
            ->whereMonth('reserva.created_at', $mes)
            ->selectRaw('
                COUNT(DISTINCT reserva.id_reserva) as total_reservas,
                COUNT(DISTINCT CASE WHEN reserva.estado = \'completada\' THEN reserva.id_reserva END) as reservas_completadas,
                COALESCE(SUM(CASE WHEN pago.estado = \'pagado\' THEN pago.monto_total ELSE 0 END), 0) as ingresos_totales,
                COALESCE(SUM(CASE WHEN pago.tipo_pago = \'anticipo\' AND pago.estado = \'pagado\' THEN pago.monto_total ELSE 0 END), 0) as anticipos_recibidos,
                COALESCE(SUM(CASE WHEN pago.tipo_pago = \'pago_completo\' AND pago.estado = \'pagado\' THEN pago.monto_total ELSE 0 END), 0) as pagos_finales,
                COALESCE(SUM(CASE WHEN pago.estado = \'pendiente\' THEN pago.monto_total ELSE 0 END), 0) as pagos_pendientes
            ')
            ->first();
        //dd($ingresosMensuales);    

        // Ranking de barberos
        $rankingBarberosaRaw = Barbero::query()
            ->leftJoin('reserva', 'barbero.id_barbero', '=', 'reserva.id_barbero')
            ->whereBetween('reserva.created_at', [$fechaInicio, $fechaFin])
            ->select('barbero.id_barbero', 'barbero.especialidad')
            ->selectRaw('
                COUNT(DISTINCT reserva.id_reserva) as total_servicios,
                COUNT(DISTINCT CASE WHEN reserva.estado = \'completada\' THEN reserva.id_reserva END) as servicios_completados,
                COALESCE(SUM(CASE WHEN reserva.estado = \'completada\' THEN reserva.total ELSE 0 END), 0) as ingresos_generados,
                COALESCE(AVG(CASE WHEN reserva.estado = \'completada\' THEN reserva.total END), 0) as promedio_por_servicio
            ')
            ->groupBy('barbero.id_barbero', 'barbero.especialidad')
            ->orderByDesc('servicios_completados')
            ->orderByDesc('ingresos_generados')
            ->get();

        $rankingBarberos = $rankingBarberosaRaw->map(function ($barbero) {
            $user = Barbero::find($barbero->id_barbero)->user;
            return [
                'id_barbero' => $barbero->id_barbero,
                'nombre' => $user->name ?? 'N/A',
                'especialidad' => $barbero->especialidad,
                'total_servicios' => $barbero->total_servicios ?? 0,
                'servicios_completados' => $barbero->servicios_completados ?? 0,
                'ingresos_generados' => floatval($barbero->ingresos_generados ?? 0),
                'promedio_por_servicio' => floatval($barbero->promedio_por_servicio ?? 0),
            ];
        });

        // Servicios más populares
        $serviciosMasPopulares = Servicio::query()
            ->leftJoin('reserva', 'servicio.id_servicio', '=', 'reserva.id_servicio')
            ->whereBetween('reserva.created_at', [$fechaInicio, $fechaFin])
            ->where('servicio.estado', 'activo')
            ->selectRaw('
                servicio.id_servicio,
                servicio.nombre,
                servicio.precio,
                servicio.duracion_minutos,
                COUNT(reserva.id_reserva) as veces_solicitado,
                COUNT(CASE WHEN reserva.estado = \'completada\' THEN 1 END) as veces_completado,
                COALESCE(SUM(CASE WHEN reserva.estado = \'completada\' THEN reserva.total ELSE 0 END), 0) as ingresos_generados
            ')
            ->groupBy('servicio.id_servicio', 'servicio.nombre', 'servicio.precio', 'servicio.duracion_minutos')
            ->orderByDesc('veces_completado')
            ->orderByDesc('ingresos_generados')
            ->limit(5)
            ->get()
            ->map(function ($servicio) {
                return [
                    'id_servicio' => $servicio->id_servicio,
                    'nombre' => $servicio->nombre,
                    'precio' => floatval($servicio->precio),
                    'duracion_minutos' => $servicio->duracion_minutos,
                    'veces_solicitado' => $servicio->veces_solicitado ?? 0,
                    'veces_completado' => $servicio->veces_completado ?? 0,
                    'ingresos_generados' => floatval($servicio->ingresos_generados ?? 0),
                ];
            });

        // Clientes frecuentes
        $clientesFrecuentesRaw = Cliente::query()
            ->leftJoin('reserva', 'cliente.id_cliente', '=', 'reserva.id_cliente')
            ->whereBetween('reserva.created_at', [$fechaInicio, $fechaFin])
            ->select('cliente.id_cliente')
            ->selectRaw('
                COUNT(reserva.id_reserva) as total_reservas,
                COUNT(CASE WHEN reserva.estado = \'completada\' THEN 1 END) as reservas_completadas,
                COALESCE(SUM(CASE WHEN reserva.estado = \'completada\' THEN reserva.total ELSE 0 END), 0) as gasto_total,
                COALESCE(AVG(CASE WHEN reserva.estado = \'completada\' THEN reserva.total END), 0) as promedio_por_visita,
                MAX(reserva.created_at) as ultima_visita
            ')
            ->groupBy('cliente.id_cliente')
            ->havingRaw('COUNT(reserva.id_reserva) > 0')
            ->orderByDesc('reservas_completadas')
            ->orderByDesc('gasto_total')
            ->limit(10)
            ->get();

        // Cargar los usuarios para los clientes
        $clienteIds = $clientesFrecuentesRaw->pluck('id_cliente');
        $usuarios = \App\Models\User::whereIn('id', function($query) use ($clienteIds) {
            $query->select('id_usuario')
                  ->from('cliente')
                  ->whereIn('id_cliente', $clienteIds);
        })->get()->keyBy('id');

        $clientesFrecuentes = $clientesFrecuentesRaw->map(function ($cliente) use ($clienteIds) {
            $user = Cliente::find($cliente->id_cliente)->user;
            return [
                'id_cliente' => $cliente->id_cliente,
                'nombre' => $user->name ?? 'N/A',
                'email' => $user->email ?? 'N/A',
                'telefono' => $user->phone ?? 'N/A',
                'total_reservas' => $cliente->total_reservas ?? 0,
                'reservas_completadas' => $cliente->reservas_completadas ?? 0,
                'gasto_total' => floatval($cliente->gasto_total ?? 0),
                'promedio_por_visita' => floatval($cliente->promedio_por_visita ?? 0),
                'ultima_visita' => $cliente->ultima_visita,
            ];
        });

        // Distribución de estados
        $distribucionEstados = Reserva::query()
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('
                COUNT(*) as total_reservas,
                COUNT(CASE WHEN estado = \'completada\' THEN 1 END) as completadas,
                COUNT(CASE WHEN estado = \'cancelada\' THEN 1 END) as canceladas,
                COUNT(CASE WHEN estado = \'no_asistio\' THEN 1 END) as no_asistio,
                COUNT(CASE WHEN estado = \'confirmada\' THEN 1 END) as confirmadas
            ')
            ->first();

        // Distribución de métodos de pago
        $distribucionMetodosPago = Pago::query()
            ->join('reserva', 'pago.id_reserva', '=', 'reserva.id_reserva')
            ->whereBetween('reserva.created_at', [$fechaInicio, $fechaFin])
            ->where('pago.estado', 'pagado')
            ->selectRaw('
                pago.metodo_pago,
                COUNT(*) as cantidad_pagos,
                COALESCE(SUM(pago.monto_total), 0) as monto_total,
                COALESCE(AVG(pago.monto_total), 0) as promedio_por_pago
            ')
            ->groupBy('pago.metodo_pago')
            ->orderByDesc('monto_total')
            ->get()
            ->map(function ($metodo) {
                return [
                    'metodo_pago' => $metodo->metodo_pago,
                    'cantidad_pagos' => $metodo->cantidad_pagos ?? 0,
                    'monto_total' => floatval($metodo->monto_total ?? 0),
                    'promedio_por_pago' => floatval($metodo->promedio_por_pago ?? 0),
                ];
            });

        return Inertia::render('Reportes/Index', [
            'pagosData' => $pagosData,
            'reservasData' => $reservasData,
            'ingresosMensuales' => $ingresosMensuales,
            'rankingBarberos' => $rankingBarberos,
            'serviciosMasPopulares' => $serviciosMasPopulares,
            'clientesFrecuentes' => $clientesFrecuentes,
            'distribucionEstados' => $distribucionEstados,
            'distribucionMetodosPago' => $distribucionMetodosPago,
        ]);
    }
}
