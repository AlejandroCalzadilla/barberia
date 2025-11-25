<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function __construct()
    {
       /*  $this->middleware('permission:usuarios.view')->only(['index']);
        $this->middleware('permission:usuarios.create')->only(['create','store']);
        $this->middleware('permission:usuarios.update')->only(['edit','update']);
        $this->middleware('permission:usuarios.delete')->only(['destroy']); */
    }

    public function index(Request $request)
    {
        $q = $request->string('q');
        $tipoFilter = $request->string('tipo');

        $usuarios = User::query()
            ->with('roles:id,name')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'ilike', "%{$q}%")
                        ->orWhere('email', 'ilike', "%{$q}%")
                        ->orWhere('telefono', 'ilike', "%{$q}%");
                });
            })
            ->when($tipoFilter->isNotEmpty(), fn($query) => $query->where('tipo_usuario', $tipoFilter))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'filters' => [
                'q' => $q,
                'tipo' => $tipoFilter,
            ],
        ]);
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get(['id','name']);
        return Inertia::render('Usuarios/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'tipo_usuario' => ['required', 'in:propietario,barbero,cliente'],
            'estado' => ['nullable', 'in:activo,inactivo'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $data['password'] = Hash::make($data['password']);
        
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $usuario = User::create($data);

        if (!empty($roles)) {
            $usuario->syncRoles($roles);
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente');
    }

    public function edit(User $usuario)
    {
        $roles = Role::orderBy('name')->get(['id','name']);
        
        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario->load('roles:id,name'),
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'tipo_usuario' => ['required', 'in:propietario,barbero,cliente'],
            'estado' => ['nullable', 'in:activo,inactivo'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $usuario->update($data);

        if (!empty($roles)) {
            $usuario->syncRoles($roles);
        }

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $usuario)
    {
        try {
            $usuario->delete();
            return redirect()
                ->route('usuarios.index')
                ->with('success', 'Usuario eliminado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }
}
