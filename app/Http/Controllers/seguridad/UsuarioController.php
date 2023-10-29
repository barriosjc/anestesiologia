<?php

namespace App\Http\Controllers\seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\user;
use App\Models\Role;
use App\Models\Grupal;
// use App\Models\Permission;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use App\Exports\UsuariosExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Mail\registerMailable;
use Illuminate\Support\Facades\Mail;

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
            $user = User::where('empresas_id', session('empresa')->id)
                ->where('name', 'LIKE', "%$keyword%")
                ->orWhere('last_name', 'LIKE', "%$keyword%")
                ->orWhere('email_verified_at', 'LIKE', "%$keyword%")
                ->orWhere('area', 'LIKE', "%$keyword%")
                ->orWhere('email', 'LIKE', "%$keyword%")
                ->orderby("last_name")
                ->latest()->simplepaginate($perPage);
        } else {
            $user = User::where('empresas_id', session('empresa')->id)
                ->simplepaginate($perPage);
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

        if (session()->has('empresa')) {
            $empresas = empresa::where("id", session('empresa')->id)->get();
        } else {
            $empresas = empresa::all();
        }
        $user = new user();
        $perfiles = role::v_roles_empresas(session('empresa')->id)->get();
        $perfiles_user = '';

        return view('seguridad.usuario.create')->with(compact('empresas', 'user', 'perfiles', 'perfiles_user'));
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
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->id,
            'empresas_id' => 'required',
            'cargo' => 'nullable|string|max:45',
            'observaciones' => 'nullable|max:1000',
            'jefe_user_id' => 'nullable',
            'es_jefe' => 'nullable',
            'telefono' => 'nullable',
            'area' => 'nullable',
        ]);

        $validated['es_jefe'] = isset($validated['es_jefe']) ? 1 : 0;
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
        
        $correo = new registerMailable($user, session('empresa'));
        Mail::send([], [], function ($message)  use ($request, $correo) {
            $message->to($request->email, $request->last_name)
                ->subject('Registro de usuario para ingreso al portal de reconocimientos !')
                ->setBody($correo->render(), 'text/html');
        });

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

        $empresas = empresa::where("id", session('empresa')->id)->get();
        $perfiles = role::v_roles_empresas(session('empresa')->id)->get();

        // $perfiles_user = $user->roles;
        $perfiles_user = '';
        foreach ($user->roles as $key => $value) {
            $perfiles_user .= $value->id . ",";
        }

        return view('seguridad.usuario.edit')->with(compact('empresas', 'user', 'perfiles', 'perfiles_user'));
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
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->id,
            'empresas_id' => 'required',
            'cargo' => 'nullable|string|max:45',
            'observaciones' => 'nullable|max:1000',
            'jefe_user_id' => 'nullable',
            'es_jefe' => 'nullable',
            'telefono' => 'nullable',
            'area' => 'nullable'
        ]);
        $validated['es_jefe'] = isset($validated['es_jefe']) ? 1 : 0;
        $validated['password'] = Hash::make('12345678');
        $validated['cambio_password'] = 1;

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
                        ->where('guard_name', session('empresa')->uri)
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
            ->where('guard_name', session('empresa')->uri)
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
                            ->where('guard_name', session('empresa')->uri)
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
            ->where('guard_name', session('empresa')->uri)
            ->get();
            // ->simplepaginate(5);
        $esabm = false;

        $titulo = 'asignados al uzuario  ->   ' . strtoupper($user->name);
        $padre = "usuarios";

        return view('seguridad.permisos.index',  compact('padre', 'usuid', 'permisos', 'permisoss', 'esabm', 'titulo'));
    }

    public function importar()
    {

        return view('seguridad.usuario.importar');
    }

    public function exportar()
    {
        return Excel::download(new UsuariosExport, 'users.xlsx');
    }

    public function subir_datos(Request $request)
    {
        if ($request->hasFile('usuarios')) {
            $uploadedFile = $request->file('usuarios');
            if ($uploadedFile->getClientOriginalExtension() === 'xlsx') {
                $excelData = Excel::toArray([], $uploadedFile);
                foreach ($excelData[0] as $index => $row) {
                    if ($index === 0) {
                        continue;
                    }
                    $grupal_id = null;
                    if (!empty($row[3])) {
                        $grupal =  grupal::where('descripcion', $row[3])->first();
                        if ($grupal) {
                            $grupal_id = $grupal->id;
                        }
                    }
                    $jefe_user_id = null;
                    if (!empty($row[13])) {
                        $usuario = user::where('email', $row[13])->first();
                        if($usuario) {
                            $jefe_user_id = $usuario->id;
                        }
                    }
                    $user = user::where('id',  $row[0])->first();
                    if (empty($user)) {
                        $user = new user;
                        $user->empresas_id = session('empresa')->id;
                        $user->password =  Hash::make('12345678');
                    }else {
                        DB::delete('delete from model_has_roles
                        where model_id = ' . $row[0]);
                    }
                    $user->id = $row[0];
                    $user->last_name = $row[1];
                    $user->name = $row[2];
                    $user->email = $row[9];
                    $user->grupal_id = $grupal_id;
                    $user->jefe_user_id = $jefe_user_id;
                    $user->telefono = $row[10];
                    $user->es_jefe = empty($row[11]) ? 0 : 1;
                    $user->foto = 'fotovacia.jpeg';
                    $user->area = $row[4];
                    $user->cargo = $row[3]; 
                    $user->save();
                    
                    // model_has_role
                    $role = Role::where('guard_name', session('empresa')->uri)
                                    ->where('name', $row[14])
                                    ->first();
                    $user->assignRole($role);
                    //$user->assignRole();
                    
                }
                return back()
                ->with('success', 'Se importaron los datos correctamente.');
            }
        }
        return back()
        ->with('error', 'No se ha proporcionado un archivo Excel válido');
    }
}
