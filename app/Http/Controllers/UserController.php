<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

    use Illuminate\Support\Facades\Hash;
    use Illuminate\Validation\Rules;


class UserController extends Controller
{
    /*
    public function index()
    {
        return Inertia::render('Usuarios/Index', [
            'users' => User::with(['roles', 'permissions'])->get(),
            'all_roles' => Role::all(),
            'all_permissions' => Permission::all(),
        ]);
    }*/

        // app/Http/Controllers/UserController.php

    public function index()
    {
        return Inertia::render('Usuarios/Index', [
            'users' => User::with(['roles', 'permissions'])->get()->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(), // Solo nombres
                    'permissions' => $user->getPermissionNames(), // Permisos directos
                ];
            }),
            'all_roles' => Role::all()->pluck('name'),
            'all_permissions' => Permission::all()->pluck('name'),
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'array',
            'permissions' => 'array'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->roles) $user->assignRole($request->roles);
        if ($request->permissions) $user->givePermissionTo($request->permissions);

        return back()->with('message', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'permissions' => 'array',
        ]);

        // Si el usuario que editamos es el mismo que está logueado...
        if (auth()->id() === $user->id) {
            // ...y en el request de roles no viene 'administrador'
            if (!in_array('administrador', $request->roles)) {
                return back()->with('error', 'No puedes quitarte el rol de Administrador a ti mismo para evitar bloqueos.');
            }
        }

        // syncRoles y syncPermissions reemplazan lo anterior por lo nuevo
        $user->syncRoles($request->roles);
        $user->syncPermissions($request->permissions);

        return back()->with('message', "Privilegios de {$user->name} actualizados.");
    }



    public function destroy(User $user)
    {
        // 1. Evitar suicidio administrativo
        if (auth()->id() === $user->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        // 2. (Opcional) Verificar si el usuario tiene registros vinculados
        // Si quieres ser estricto, podrías impedir el borrado si ya capturó personas.
        
        $user->delete();

        return back()->with('message', "El usuario ha sido eliminado correctamente.");
    }
}
