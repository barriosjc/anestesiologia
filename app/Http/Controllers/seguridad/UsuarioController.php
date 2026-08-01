<?php

namespace App\Http\Controllers\seguridad;

use App\Models\Role;
use App\Models\user;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class UsuarioController extends Controller
{
    public function roles(int $usuid, int $rolid = null, string $tarea = '')
    {
        $rol = role::find($rolid);
        $user = user::find($usuid);
        switch ($tarea) {
            case 'asignar':
                $a = $user->assignRole($rol);
                break;

            case 'desasignar':
                $a = $user->removeRole($rol);
                break;
        }

        $roles = $user->Roles()
                        ->get();
        //->simplepaginate(5);
        $roless = DB::table('roles')
            ->select(
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at'
            )
            ->whereNotIn('id', DB::table('model_has_roles')->select('role_id')->where('model_id', '=', $usuid))
            ->get();
            //->simplepaginate(5);
        $esabm = false;
        $padre = "usuarios";
        $titulo = 'asignados al usuario  ->   ' . strtoupper($user->name);

        return view('seguridad.roles.index', compact('padre', 'usuid', 'roles', 'roless', 'esabm', 'titulo'));
        //         ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    public function permisos(int $usuid, int $perid = null, string $tarea = '')
    {

        $user = user::find($usuid);
        $per = permission::find($perid);
        switch ($tarea) {
            case 'asignar':
                // asigna el usu
                $a = $user->givePermissionTo($per);
                break;

            case 'desasignar':
                $a = $user->revokePermissionTo($per);
                break;
        }

        $permisos = $user->permissions()
                            ->get();
        //->simplepaginate(5);
        $permisoss = DB::table('permissions')
            ->select(
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at'
            )
            ->whereNotIn('id', DB::table('model_has_permissions')->select('permission_id')->where('model_id', '=', $usuid))
            ->get();
            // ->simplepaginate(5);
        $esabm = false;

        $titulo = 'asignados al usuario  ->   ' . strtoupper($user->name);
        $padre = "usuarios";

        return view('seguridad.permisos.index', compact('padre', 'usuid', 'permisos', 'permisoss', 'esabm', 'titulo'));
    }
}
