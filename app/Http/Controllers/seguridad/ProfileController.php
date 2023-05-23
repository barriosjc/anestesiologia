<?php

namespace App\Http\Controllers\seguridad;

use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Grupal;
use App\Models\grupal_empresa;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Auth()->user()->empresas_id === 0) {
            $empresas = empresa::all();
        } else {
            $empresas = empresa::where("id", Auth()->user()->empresas_id)
                            ->get();
        }
        $user = new user();

        return view('seguridad.usuario.profile')->with(compact('empresas', 'user'));
    }

    public function save(request $request) {

        $validated = $request->validate([
            'id' => 'nullable',
            'name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->id,
            'empresas_id' => 'required',
            'grupal_id' => 'required',
            'cargo' => 'nullable|string|max:45',
            'observaciones' => 'nullable|max:255'
        ]);  
        
        $validated['password'] = Hash::make('12345678');
        $validated['cambio_password'] = 1;

        if($request->id) {
            $user = user::where("id", $request->id)->first();
        }else {
            $user = new user();
        }
        $user->save($validated);

        return redirect()->route('profile')
        ->with('success', 'Se guardó el usuario en forma correcta.');

    }

    // public function getGrupal(Request $request)
    // {  
    //         $empresas_id = $request->input('empresas_id');
    //         $grupal = grupal::v_grupal($empresas_id)->get();
    //         $response = ['data' => $grupal];

    //         return response()->json($response);
    // }

    public function grupal(Request $request)
{
    try {    
            $empresas_id = $request->input('empresas_id');
            $grupal = grupal::v_grupal($empresas_id)->get();
            $response = ['data' => $grupal];

    } catch (\Exception $exception) {
        return response()->json([ 'message' => 'There was an error retrieving the records' ], 500);
    }
    return response()->json($response);
}



    // public function update(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:50',
    //         'last_name' => 'nullable|string|max:100',
    //         'email' => 'required|string|email|max:255|unique:users,email,' . Auth::user()->id,
    //         'current_password' => 'nullable|required_with:new_password|max:255',
    //         'new_password' => 'nullable|min:8|max:12|required_with:current_password|max:255',
    //         'password_confirmation' => 'nullable|min:8|max:12|required_with:new_password|same:new_password|max:255'
    //     ]);

    //     $user = User::findOrFail(Auth::user()->id);
    //     $user->name = $request->input('name');
    //     $user->last_name = $request->input('last_name');
    //     $user->email = $request->input('email');

    //     if (!is_null($request->input('current_password'))) {
    //         if (Hash::check($request->input('current_password'), $user->password)) {
    //             $user->password = Hash::make($request->input('new_password'));
    //         } else {
    //             return redirect()->back()->withInput();
    //         }
    //     }

    //     $user->save();

    //     return redirect()->route('profile');
    // }

    // public function updatefoto(Request $rq){
    //     $rq->validate([
    //         'foto' => 'image'
    //     ]);

    //     $user = User::findOrFail(Auth::user()->id);        
    //     if ($rq->hasFile('file')){
    //         // guarda el archivo dentro de storage/app/fotos
    //         $foto_vieja = $user->foto;
    //         if(!empty($foto_vieja)){
    //             Storage::delete($foto_vieja);
    //         }
    //         $path = Storage::disk('usuarios')->put("", $rq->file('file'));
    //         // juan foto save
    //         //$path = $rq->file('file')->storeAs("","mifoto.jpg", "usuarios");
    //         //dd($path);
    //         //guarda storage/app/fotos
    //         $user->foto = $path;
    //         $user->save();

    //     }

    //     return redirect()->route('profile');
    // }
}
