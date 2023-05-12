<?php

namespace App\Http\Controllers\seguridad;

use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('seguridad.usuario.profile');
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::user()->id,
            'current_password' => 'nullable|required_with:new_password|max:255',
            'new_password' => 'nullable|min:8|max:12|required_with:current_password|max:255',
            'password_confirmation' => 'nullable|min:8|max:12|required_with:new_password|same:new_password|max:255'
        ]);

        $user = User::findOrFail(Auth::user()->id);
        $user->name = $request->input('name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');

        if (!is_null($request->input('current_password'))) {
            if (Hash::check($request->input('current_password'), $user->password)) {
                $user->password = Hash::make($request->input('new_password'));
            } else {
                return redirect()->back()->withInput();
            }
        }

        $user->save();

        return redirect()->route('profile');
    }

    public function updatefoto(Request $rq){
        $rq->validate([
            'foto' => 'image'
        ]);

        $user = User::findOrFail(Auth::user()->id);        
        if ($rq->hasFile('file')){
            // guarda el archivo dentro de storage/app/fotos
            $foto_vieja = $user->foto;
            if(!empty($foto_vieja)){
                Storage::delete($foto_vieja);
            }
            $path = Storage::disk('usuarios')->put("", $rq->file('file'));
            // juan foto save
            //$path = $rq->file('file')->storeAs("","mifoto.jpg", "usuarios");
            //dd($path);
            //guarda storage/app/fotos
            $user->foto = $path;
            $user->save();

        }

        return redirect()->route('profile');
    }
}
