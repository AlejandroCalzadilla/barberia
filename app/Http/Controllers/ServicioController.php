<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Servicio;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

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
        $categoriaId = $request->integer('categoria');

        $servicios = Servicio::query()
            ->with('categoria:id_categoria,nombre')
            ->when($q, fn($query) => $query->where('nombre', 'ilike', "%{$q}%"))
            ->when($categoriaId, fn($query) => $query->where('id_categoria', $categoriaId))
            ->orderByDesc('id_servicio')
            ->paginate(10)
            ->withQueryString();

        $categorias = Categoria::orderBy('nombre')->get(['id_categoria','nombre']);

        return Inertia::render('Servicios/Index', [
            'servicios' => $servicios,
            'categorias' => $categorias,
            'filters' => [
                'q' => $q,
                'categoria' => $categoriaId,
            ],
        ]);
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get(['id_categoria','nombre']);
        $productos = Producto::orderBy('nombre')->get(['id_producto', 'nombre', 'precio_venta']);
        
        return Inertia::render('Services/Create', [
            'categorias' => $categorias,
            'productos' => $productos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'id_categoria' => 'required|exists:categories,id_categoria',
            'precio' => 'required|numeric|min:0',
            'duracion_minutos' => 'required|integer|min:1',
            'estado' => 'required|in:activo,inactivo',
            'imagen' => 'nullable|image|max:2048',
            'productos' => 'nullable|array',
            'productos.*.id_producto' => 'required|exists:products,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        
        try {
            if ($request->hasFile('imagen')) {
                $path = $request->file('imagen')->store('services', 'public');
                $validated['imagen'] = $path;
            }

            $servicio = Servicio::create($validated);

            // Sincronizar productos relacionados
            if (!empty($validated['productos'])) {
                $productosSync = [];
                foreach ($validated['productos'] as $producto) {
                    $productosSync[$producto['id_producto']] = ['cantidad' => $producto['cantidad']];
                }
                $servicio->productos()->sync($productosSync);
            }

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
        $categorias = Categoria::orderBy('nombre')->get(['id_categoria','nombre']);
        return Inertia::render('Servicios/Edit', [
            'servicio' => $servicio->load('categoria:id_categoria,nombre'),
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'id_categoria' => 'required|exists:categories,id_categoria',
            'precio' => 'required|numeric|min:0',
            'duracion_minutos' => 'required|integer|min:1',
            'estado' => 'required|in:activo,inactivo',
            'imagen' => 'nullable|image|max:2048',
            'productos' => 'nullable|array',
            'productos.*.id_producto' => 'required|exists:products,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        
        try {
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si existe
                if ($servicio->imagen) {
                    Storage::disk('public')->delete($servicio->imagen);
                }
                $path = $request->file('imagen')->store('services', 'public');
                $validated['imagen'] = $path;
            }

            $servicio->update($validated);

            // Sincronizar productos relacionados
            $productosSync = [];
            if (!empty($validated['productos'])) {
                foreach ($validated['productos'] as $producto) {
                    $productosSync[$producto['id_producto']] = ['cantidad' => $producto['cantidad']];
                }
            }
            $servicio->productos()->sync($productosSync);

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
        $servicio->delete();
        return redirect()->route('servicios.index');
    }
}
