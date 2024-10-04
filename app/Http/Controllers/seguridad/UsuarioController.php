<?php

namespace App\Http\Controllers\seguridad;

use App\Models\Role;
use App\Models\user;
use App\Http\Requests;
use App\Models\Centro;
use App\Models\Grupal;
use Illuminate\Http\Request;
use App\Mail\registerMailable;
use App\Exports\UsuariosExport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 5;

        if (!empty($keyword)) {
            $user = User::where('name', 'LIKE', "%$keyword%")
                ->orWhere('email', 'LIKE', "%$keyword%")
                ->orderby("name")
                ->latest()->paginate($perPage);
        } else {
            $user = User::paginate($perPage);
        }

        $esabm = true;

        return view('seguridad.usuario.index', compact('user', 'esabm'))
                    ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = new user();
        $centros = Centro::orderby("nombre")->get();
        $perfiles = role::get();
        $perfiles_user = '';

        return view('seguridad.usuario.create')->with(compact('user', 'centros', 'perfiles', 'perfiles_user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->id,
            'centro_id' => 'required'
        ]);

        // $validated['activo'] = isset($validated['activo']) ? 1 : 0;
        $validated['password'] = Hash::make('12345678');
        $validated['cambio_password'] = 1;
        $validated['foto'] = 'fotovacia.jpeg';

        $request->validate([
            'perfil_id' => 'required'
        ]);

        if ($request->id) {
            $user = user::where('id', $request->id)->first();
        } else {
            $user = new user();
        }
        foreach ($validated as $key => $value) {
            $user->$key = $value;
        }
        $user->save();

        if (isset($request->perfil_id)) {
            foreach ($request->perfil_id as $key => $value) {
                $rol = role::find($value);
                $user->assignRole($rol);
            }
        }
        
        // $correo = new registerMailable($user);
        // Mail::send([], [], function ($message)  use ($request, $correo) {
        //     $message->to($request->email, $request->last_name)
        //         ->subject('Registro de usuario para ingreso al portal de reconocimientos !')
        //         ->setBody($correo->render(), 'text/html');
        // });

        return back()
            ->withInput($request->input())
            ->with('success', 'Se guardó los datos del usuario de forma correcta.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('seguridad.usuario.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $perfiles = role::v_roles()->get();
        $centros = Centro::orderby("nombre")->get();

        // $perfiles_user = $user->roles;
        $perfiles_user = '';
        foreach ($user->roles as $key => $value) {
            $perfiles_user .= $value->id . ",";
        }

        return view('seguridad.usuario.edit')->with(compact('user', 'perfiles', 'perfiles_user', 'centros'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'centro_id' => 'required',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->id,
        ]);

        if($request->has('blanquear')) {
            $validated['password'] = Hash::make('12345678');
            $validated['cambio_password'] = 1;
        }
        $request->validate([
            'perfil_id' => 'required'
        ]);

        if ($request->id) {
            $user = user::where('id', $request->id)->first();
        } else {
            $user = new user();
        }
        foreach ($validated as $key => $value) {
            $user->$key = $value;
        }
        $user->save();

        //quita los roles actuales
        $user->syncRoles([]);

        //asigna los roles marcados
        if (isset($request->perfil_id)) {
            foreach ($request->perfil_id as $key => $value) {
                $rol = role::find($value);
                $user->assignRole($rol);
            }
        }

        $esabm = true;
        return redirect()->route('usuario.index')
            ->with('success', 'Se actualizaron los datos del usuario en forma correcta.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        User::destroy($id);

        return redirect('usuario')->with('flash_message', 'Usuario borrado!');
    }

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

        return view('seguridad.permisos.index',  compact('padre', 'usuid', 'permisos', 'permisoss', 'esabm', 'titulo'));
    }

    // public function importar()
    // {

    //     return view('seguridad.usuario.importar');
    // }

    // public function exportar()
    // {
    //     return Excel::download(new UsuariosExport, 'users.xlsx');
    // }

}
