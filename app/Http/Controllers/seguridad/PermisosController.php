<?php

namespace App\Http\Controllers\seguridad;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\user;
use Illuminate\Support\Facades\DB;

class PermisosController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 5;
    
        if (!empty($keyword)) {
          $permisos = Permission::where('name', 'LIKE', "%$keyword%")
            ->orWhere('guard_name', 'LIKE', "%$keyword%")
            ->latest()->simplepaginate($perPage);
        } else {
          $permisos = Permission::orderBy('id','DESC')->latest()->simplepaginate($perPage);
        }
        $esabm = true;

        return view('seguridad.permisos.index',compact('permisos','esabm'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $permission = Permission::get();
        return view('seguridad.permisos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|max:255|unique:permissions,name',
            'guard_name' => 'required|max:255'
        ]);

        $permisos = Permission::create(['name' => $request->input('name'),
                                'guard_name' => $request->input('guard_name')]);
        //$permisos->syncPermissions($request->input('permission'));
        $esabm = true;

        return redirect()->route('permisos.index',compact('esabm'))
                        ->with('flash_message','Permiso creado correctamente');
    }

    public function show($id)
    {
        $permisos = Permission::findOrfail($id);

        return view('seguridad.permisos.show',compact('permisos'));
    }

    public function edit($id)
    {
        $permisos = Permission::findOrFail($id);

        return view('seguridad.permisos.edit',compact('permisos'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255|unique:permissions,name,'. $id,
            'guard_name' => 'required|max:255',
        ]);

        $permisos = Permission::find($id);
        $permisos->name = $request->input('name');
        $permisos->guard_name = $request->input('guard_name');
        $permisos->save();
        $esabm = true;
        //$permisos->syncPermissions($request->input('permission'));

        return redirect()->route('permisos.index',compact('esabm'))
                        ->with('flash_message','Permiso modificado correctamente');
    }

    public function destroy($id)
    {
       // DB::table("roles")->where('id',$id)->delete();
        Permission::find($id)->delete();

        return redirect()->route('permisos.index')
                        ->with('flash_message','Permiso borrado correctamente');
    }

    public function usuarios(int $perid, int $usuid = null, string $tarea = ''){

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
            ->select( 'id', 'name', 'last_name', 'email',
                        'email_verified_at', 'password', 'remember_token',
                        'foto', 'created_at', 'updated_at', 'deleted_at')
            ->whereNotIn('id', DB::table('model_has_permissions')->select('model_id')->where('permission_id', '=', $perid))
            ->simplepaginate(5);
        $esabm = false;

        $titulo = 'asignados al permiso  ->   ' . strtoupper($per->name);
        $padre = "permisos";
        // $rolid = $roles->id;

        return view('seguridad.usuario.index',  compact('padre', 'perid', 'user', 'users', 'esabm', 'titulo'));   

    }

    public function roles(int $perid, int $rolid = null, string $tarea = ''){

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

    $roles = $per->Roles()->simplepaginate(5);
    $roless = DB::table('roles')                 
    ->select( 'id', 'name', 'guard_name', 
                'created_at', 'updated_at')
    ->whereNotIn('id', DB::table('role_has_permissions')->select('role_id')->where('permission_id', '=', $perid))
    ->simplepaginate(5);
    $esabm = false;
    $padre = "permisos";
    $titulo = 'asignados al permiso  ->   ' . strtoupper($per->name);

    return view('seguridad.roles.index',compact('padre', 'perid', 'roles', 'roless', 'esabm', 'titulo'));
 
    }

    
}