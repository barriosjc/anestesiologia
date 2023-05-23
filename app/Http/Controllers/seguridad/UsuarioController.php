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
    return view('seguridad.usuario.create');
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
    $this->validate($request, [
      'name' => 'required|max:60',
      'last_name' => 'required|max:60',
      'password' => 'required',
      'email' => 'required'
    ]);

    $requestData = $request->all();
    if ($request->hasFile('avatar')) {
      $requestData['foto'] = $request->file('foto')->store('fotos');
    }
    $requestData['password'] = Hash::make($request->password);

    User::create($requestData);

    return redirect('usuario')->with('flash_message', 'Usuario agregado con éxito!');
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

    return view('seguridad.usuario.edit', compact('user'));
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
    $this->validate($request, [
      'name' => 'required|max:50',
      'last_name' => 'required|max:100',
      'password' => 'required|max:255',
      'email' => 'required|max:255'
    ]);

    $requestData = $request->all();
    $user = User::findOrFail($id);

    if ($request->hasFile('foto')) {
      $foto_vieja = $user->foto;
      if (!empty($foto_vieja) and $foto_vieja <> config('app.avatar-def')) {
        Storage::delete($foto_vieja);
      }

      //guarda storage/app/public/fotos
      $path = Storage::disk('usuarios')->put("", $request->file('foto'));
      //$path = $request->file('foto')->store('', 'usuarios');
      //$user->foto = $path;
      $requestData['foto']= $path;
      // $user->save();    
    }
    $requestData['password']  = Hash::make($request->password);

    $user->update($requestData);
    $esabm = true;
    $user = User::latest()->paginate(10);

    return view('seguridad.usuario.index', compact('user', 'esabm'))
      ->with('flash_message', 'Usuario actualizado!');
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
