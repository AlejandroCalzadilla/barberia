<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Servicio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServicioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:servicios.view')->only(['index']);
        $this->middleware('permission:servicios.create')->only(['create','store']);
        $this->middleware('permission:servicios.update')->only(['edit','update']);
        $this->middleware('permission:servicios.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $q = $request->string('q');

        $servicios = Servicio::when($q, fn($query) => 
            $query->where('nombre', 'like', "%$q%")
                  ->orWhere('descripcion', 'like', "%$q%")
        )
        ->orderBy('nombre')
        ->paginate(10);
       
        return Inertia::render('Servicios/Index', [
            'servicios' => $servicios,
            'filters' => [
                'q' => $q,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Servicios/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'duracion_minutos' => 'required|integer|min:1',
            'estado' => 'required|in:activo,inactivo',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        DB::beginTransaction();
        
        try {
            // Procesar imagen si existe
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                $ruta = $imagen->storeAs('servicios', $nombreImagen, 'public');
                $validated['imagen'] = '/storage/' . $ruta;
            }

            Servicio::create($validated);

            DB::commit();

            return redirect()
                ->route('servicios.index')
                ->with('success', 'Servicio creado correctamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el servicio: ' . $e->getMessage());
        }
    }

    public function edit(Servicio $servicio)
    {
        return Inertia::render('Servicios/Edit', [
            'servicio' => $servicio,
        ]);
    }

    public function update(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'duracion_minutos' => 'required|integer|min:1',
            'estado' => 'required|in:activo,inactivo',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // Procesar imagen si existe
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si existe
                if ($servicio->imagen) {
                    $rutaAntigua = str_replace('/storage/', '', $servicio->imagen);
                    \Storage::disk('public')->delete($rutaAntigua);
                }
                
                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                $ruta = $imagen->storeAs('servicios', $nombreImagen, 'public');
                $validated['imagen'] = '/storage/' . $ruta;
            }

            $servicio->update($validated);

            DB::commit();

            return redirect()
                ->route('servicios.index')
                ->with('success', 'Servicio actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el servicio: ' . $e->getMessage());
        }
    }

    public function destroy(Servicio $servicio)
    {
        try {
            $servicio->delete();
            return redirect()
                ->route('servicios.index')
                ->with('success', 'Servicio eliminado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el servicio: ' . $e->getMessage());
        }
    }
}

