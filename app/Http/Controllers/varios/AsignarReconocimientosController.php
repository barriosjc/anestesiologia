<?php

namespace App\Http\Controllers\varios;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\reconocimiento;
use App\Models\User;


class AsignarReconocimientosController extends Controller
{
     /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\View\View
   */
  public function index(Request $request)
  {
     $perPage = 5;

    if (!empty($keyword)) {
      $reconocimientos = reconocimiento::v_reconocimientos()
        ->where('empresas_id',session('empresa')->id)
        ->where('email', 'LIKE', "%$keyword%")
        ->orWhere('last_name', 'LIKE', "%$keyword%")
        ->orWhere('motivo', 'LIKE', "%$keyword%")
        ->orderby("last_name")
        ->latest()->simplepaginate($perPage);
    } else {
      $reconocimientos = reconocimiento::v_reconocimientos()
                ->where('empresas_id', session('empresa')->id)
                ->simplepaginate($perPage);
    }

    $users = user::where('empresas_id', session('empresa')->id)->get();

    return view('varios.reconocimientos', compact('reconocimientos', 'users'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\View\View
   */
  
  /**
   * save a newly created resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
   */
  public function save(Request $request)
  {
    $validated = $request->validate([
      'motivo' => 'required|string|max:50',
      'users_id' => 'required',
    ]);

      $reconocimiento = new reconocimiento();
    foreach ($validated as $key => $value) {
      $reconocimiento->$key = $value;
    }
    $reconocimiento->save();

    return back()
      ->withInput($request->input())
      ->with('success', 'Se guardó los datos de forma correcta.');
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
    reconocimiento::destroy($id);

    return back()
      ->with('flash_message', 'reconocimiento borrado!');
  }

}