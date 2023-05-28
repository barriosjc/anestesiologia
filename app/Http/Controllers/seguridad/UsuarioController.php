<?php

namespace App\Http\Controllers\seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\user;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

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
    $perPage = 10;

    if (!empty($keyword)) {
      $user = User::where('name', 'LIKE', "%$keyword%")
        ->orWhere('last_name', 'LIKE', "%$keyword%")
        ->orWhere('email_verified_at', 'LIKE', "%$keyword%")
        // ->orWhere('tipo', 'LIKE', "%$keyword%")
        // ->orWhere('direccion', 'LIKE', "%$keyword%")
        // ->orWhere('localidad', 'LIKE', "%$keyword%")
        ->orWhere('email', 'LIKE', "%$keyword%")
        // ->orWhere('telefono', 'LIKE', "%$keyword%")
        // ->orWhere('observacion', 'LIKE', "%$keyword%")
        ->orderby("last_name")
        ->latest()->paginate($perPage);
    } else {
      $user = User::latest()->paginate($perPage);
    }

    $esabm = true;

    return view('seguridad.usuario.index', compact('user', 'esabm'));
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
    $perfiles = role::all();
    $perfiles_user = [];

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
      'observaciones' => 'nullable|max:255',
      'jefe_user_id' => 'nullable',
      'es_jefe' => 'nullable',
      'telefono' => 'nullable',
    ]);
    $validated['es_jefe'] = isset($validated['es_jefe']) ? 1 : 0;
    $validated['password'] = Hash::make('12345678');
    $validated['cambio_password'] = 1;
    $validated['foto'] = 'fotovacia.jpeg';

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

    if (session()->has('empresa')) {
      $empresas = empresa::where("id", session('empresa')->id)->get();
    } else {
      $empresas = empresa::all();
    }
    $perfiles = role::all();
    // $perfiles_user = $user->roles;
    $perfiles_user = [];
    foreach ($user->roles as $key => $value) {
        $perfiles_user[] += $value->id;
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
      'observaciones' => 'nullable|max:255',
      'jefe_user_id' => 'nullable',
      'es_jefe' => 'nullable',
      'telefono' => 'nullable',
    ]);
    $validated['es_jefe'] = isset($validated['es_jefe']) ? 1 : 0;
    $validated['password'] = Hash::make('12345678');
    $validated['cambio_password'] = 1;
    if ($request->id) {
      $user = user::where('id', $request->id)->first();
    } else {
      $user = new user();
    }
    foreach ($validated as $key => $value) {
      $user->$key = $value;
    }
    $user->save();

    foreach (role::all() as $role) {
      $user->removeRole($role->name);
    }
    if (isset($request->perfil_id)) {
      foreach ($request->perfil_id as $key => $value) {
        $rol = role::find($value);
        $user->assignRole($rol);
      }
    }
    
    $esabm = true;
    $user = User::latest()->paginate(10);

    return view('seguridad.usuario.index', compact('user', 'esabm'))
      ->with('success', 'Usuario actualizado!');
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

    $roles = $user->Roles()->paginate(25);
    $roless = DB::table('roles')
      ->select(
        'id',
        'name',
        'guard_name',
        'created_at',
        'updated_at'
      )
      ->whereNotIn('id', DB::table('model_has_roles')->select('role_id')->where('model_id', '=', $usuid))
      ->paginate(25);
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

    $permisos = $user->permissions()->paginate(25);
    $permisoss = DB::table('permissions')
      ->select(
        'id',
        'name',
        'guard_name',
        'created_at',
        'updated_at'
      )
      ->whereNotIn('id', DB::table('model_has_permissions')->select('permission_id')->where('model_id', '=', $usuid))
      ->paginate(25);
    $esabm = false;

    $titulo = 'asignados al uzuario  ->   ' . strtoupper($user->name);
    $padre = "usuarios";

    return view('seguridad.permisos.index',  compact('padre', 'usuid', 'permisos', 'permisoss', 'esabm', 'titulo'));
  }
}
