<?php

namespace App\Http\Controllers\seguridad;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermisosController extends Controller
{
    public function usuarios(Request $request, int $perid, int $usuid = null, string $tarea = '')
    {

        $per = permission::find($perid);
        $user = user::find($usuid);
        switch ($tarea) {
            case 'asignar':
                // asigna el rol
                $a = $user->givePermissionTo($per);
                break;

            case 'desasignar':
                $a = $user->revokePermissionTo($per);
                break;
        }

        $user = $per->users()->simplepaginate(5);
        $users = DB::table('users')
            ->select(
                'id',
                'name',
                'email',
                'email_verified_at',
                'password',
                'remember_token',
                'foto',
                'created_at',
                'updated_at',
                'deleted_at'
            )
            ->whereNotIn('id', DB::table('model_has_permissions')->select('model_id')->where('permission_id', '=', $perid))
            ->get();
            // ->simplepaginate(5);
        $esabm = false;

        $titulo = 'asignados al permiso  ->   ' . strtoupper($per->name);
        $padre = "permisos";
        // $rolid = $roles->id;

        return view('seguridad.usuario.index',  compact('padre', 'perid', 'user', 'users', 'esabm', 'titulo')) ;
        // ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    public function roles(int $perid, int $rolid = null, string $tarea = '')
    {

        $per = permission::find($perid);
        $rol = role::find($rolid);
        switch ($tarea) {
            case 'asignar':
                // asigna el rol
                $a = $per->assignRole($rol);
                break;

            case 'desasignar':
                $a = $per->removeRole($rol);
                break;
        }

        $roles = $per->Roles()
                    ->get();
        // ->simplepaginate(5);
        $roless = DB::table('roles')
            ->select(
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at'
            )
            ->whereNotIn('id', DB::table('role_has_permissions')->select('role_id')->where('permission_id', '=', $perid))
            ->where('guard_name', "web")
            ->get();
            // ->simplepaginate(5);
        $esabm = false;
        $padre = "permisos";
        $titulo = 'asignados al permiso  ->   ' . strtoupper($per->name);

        return view('seguridad.roles.index', compact('padre', 'perid', 'roles', 'roless', 'esabm', 'titulo'));
    }
}
