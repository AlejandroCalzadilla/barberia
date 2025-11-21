<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\BarberoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ReporteController;
use App\Models\Servicio;
use App\Models\Barbero;
use App\Models\Horario;
use App\Models\Reserva;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Rutas públicas para el catálogo de servicios - API JSON
Route::get('/api/servicios-catalogo', function () {
    return response()->json(
        Servicio::get()
    );
})->name('servicios.catalogo.api');

Route::get('/api/barberos-disponibles', function () {
    return response()->json(
        Barbero::with('user:id,name', 'horarios')
            ->where('estado', 'activo')
            ->get()
    );
})->name('barberos.disponibles.api');

Route::get('/api/horarios-disponibles', function () {
    $barberoId = request()->integer('barbero_id');
    $fecha = request()->input('fecha');

    if (!$barberoId || !$fecha) {
        return response()->json([]);
    }

    // Obtener horarios del barbero para ese día
    $diaSemana = strtolower(Carbon::parse($fecha)->locale('es')->dayName);

    $horariosDelDia = Horario::where('id_barbero', $barberoId)
        ->where('dia_semana', $diaSemana)
        ->where('estado', 'activo')
        ->get();

    if ($horariosDelDia->isEmpty()) {
        return response()->json([]);
    }

    // Generar horarios disponibles cada 30 minutos
    $horariosDisponibles = [];

    foreach ($horariosDelDia as $horario) {
        $inicio = Carbon::parse($horario->hora_inicio);
        $fin = Carbon::parse($horario->hora_fin);
        $duracionServicio = 30; // minutos por defecto

        while ($inicio->copy()->addMinutes($duracionServicio) <= $fin) {
            // Verificar si hay reserva en ese horario
            $tieneReserva = Reserva::where('id_barbero', $barberoId)
                ->where('fecha_reserva', $fecha)
                ->where('hora_inicio', $inicio->format('H:i'))
                ->whereNotIn('estado', ['cancelada', 'no_asistio'])
                ->exists();

            if (!$tieneReserva) {
                $horariosDisponibles[] = $inicio->format('H:i');
            }

            $inicio->addMinutes(30);
        }
    }

    return response()->json(array_values(array_unique($horariosDisponibles)));
})->name('horarios.disponibles.api');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Ruta específica para catálogo de servicios (solo clientes)
    Route::get('/servicios-catalogo', function () {
        return Inertia::render('Servicios/Catalogo');
    })->name('servicios.catalogo');

    Route::resource('categorias', CategoriaController::class);
    Route::resource('productos', ProductoController::class);
    Route::resource('servicios', ServicioController::class);
    Route::resource('barberos', BarberoController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('horarios', HorarioController::class);
    Route::resource('reservas', ReservaController::class);
    Route::resource('pagos', PagoController::class);
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
});
