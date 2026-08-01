<?php

namespace App\Http\Controllers\seguridad;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\user;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function usuarios(int $rolid, $usuid = 0, string $tarea = '')
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

        $user = DB::table('users')
                ->select(
                    'users.id',
                    'users.name',
                    'email',
                    'email_verified_at',
                    'password',
                    'remember_token',
                    'foto',
                    'users.created_at',
                    'users.updated_at',
                    'users.deleted_at'
                )
                ->join('model_has_roles as mr', 'mr.model_id', 'users.id')
                ->where('mr.role_id', '=', $rol->id)
                ->get();
                // ->simplepaginate(5);

//        $user = $rol->users()->simplepaginate(5);
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
            ->whereNotIn('id', DB::table('model_has_roles')->select('model_id')->where('role_id', '=', $rolid))
            ->get();
            // ->simplepaginate(5);
        $esabm = false;
        $titulo = 'asignados al rol  ->   ' . strtoupper($rol->name);
        $padre = "roles";
        // $rolid = $roles->id;

        return view('seguridad.usuario.index', compact('padre', 'rolid', 'user', 'users', 'esabm', 'titulo'));
    }

    public function permisos(int $rolid, int|string $perid, string $tarea = '')
    {

        $rol = role::find($rolid);
        $per = permission::find($perid);
        switch ($tarea) {
            case 'asignar':
                // asigna el rol
                $a = $rol->givePermissionTo($per);
                break;

            case 'desasignar':
                $a = $rol->revokePermissionTo($per);
                break;
        }

        $permisos = $rol->permissions()
                            ->get();
        // ->simplepaginate(5);
        $permisoss = DB::table('permissions')
            ->select(
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at'
            )
            ->whereNotIn('id', DB::table('role_has_permissions')->select('permission_id')->where('role_id', '=', $rolid))
            ->where('guard_name', "web")
            ->get();
            // ->simplepaginate(5);
        $esabm = false;

        $titulo = 'asignados al rol  ->   ' . strtoupper($rol->name);
        $padre = "roles";
        // $rolid = $roles->id;

        return view('seguridad.permisos.index', compact('padre', 'rolid', 'permisos', 'permisoss', 'esabm', 'titulo'));
    }
}
