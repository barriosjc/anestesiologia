<?php

namespace App\Http\Controllers\seguridad;

use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Empresa;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id)
    {
        $user = user::where("id", $id)->first();
        if ($user->empresas_id === 0) {
            $empresas = empresa::all();
        } else {
            $empresas = empresa::where("id", $user->empresas_id)->get();
        }
   
        $jefes = user::where('es_jefe', 1)
                    ->where('id', $user->empresas_id)
                    ->get();

        return view('seguridad.usuario.perfil')->with(compact('empresas', 'user', 'jefes'));
    }

    public function nuevo()
    {
        if (Auth()->user()->empresas_id === 0) {
            $empresas = empresa::all();
        } else{
            $empresas = empresa::where("id", Auth()->user()->empresas_id)->get();
        }
        $user = new user();
        $jefes = user::where('es_jefe', 1)
                    ->where('id', $user->empresas_id)
                    ->get();

        return view('seguridad.usuario.perfil')->with(compact('empresas', 'user', 'jefes'));
    }

    public function save(request $request)
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

        return back()
            ->withInput($request->input())
            ->with('success', 'Se guardó los datos del usuario en forma correcta.');
    }

    public function usuarios_jefes(Request $request)
    {
        try {
            $empresas_id = $request->input('empresas_id');
            $grupal = user::where('empresas_id', $empresas_id)
                                ->where('es_jefe', 1)->get();
            $response = ['data' => $grupal];

        } catch (\Exception $exception) {
            return response()->json(['message' => 'hay un error al intentar traer los usuarios jefes'], 500);
        }
        return response()->json($response);
    }

    public function foto(Request $request)
    {
        $request->validate([
            "id" => 'required',
            'foto' => 'image'
        ],[
            'id.required' => 'Primero debe tener guardado Detalle de usuario para luego poder subir una foto'
        ]);

        $user = User::findOrFail($request->id);
        if ($request->hasFile('foto')) {
            // guarda el archivo dentro de storage/app/fotos
            $foto_vieja = $user->foto;
            if (!empty($foto_vieja)) {
                Storage::delete($foto_vieja);
            }
            $path = Storage::disk('usuarios')->put("", $request->file('foto'));
            $user->foto = $path;
            $user->save();
        }

        return back()
            ->withInput($request->input())
            ->with('success', 'Foto actualizada correctamente.');
    }

    public function password() {
        $titulo = "Cambio de clave";

        return view('seguridad.usuario.password')->with(compact('titulo'));
    }

    public function save_password(Request $request) {
        $validado = $request->validate([
            'password_nueva' => ['required', 'string', 'min:8', 'max:20', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
            'confirmacion_password' => ['required', 'string', 'min:8', 'max:20', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', 
            'same:password_nueva'],
            'password_actual' => ['required', 'string', 'max:20',
                function ($attribute, $value, $fail) use ($request) {
                            $usuario = User::where('id', Auth()->user()->id)->first();
                            // dd($usuario ,  Hash::make($value))
                            if (!($usuario && Hash::make($value) != $usuario->password)) {
                                $fail('La clave actual que ingreso es incorrecta.');
                            }
                        }]
        ]);

        $user = user::where("id", Auth()->user()->id)->first();
        $user->password = hash::make($validado['password_nueva']);
        $user->save();

        return back()->with('success', 'Se actualizó la nueva password correctamente.');

    }
}
