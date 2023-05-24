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
        $user = user::find(Auth()->user()->id);
        $jefes = user::where('es_jefe', 1)->get();

        return view('seguridad.usuario.profile')->with(compact('empresas', 'user', 'jefes'));
    }

    public function nuevo()
    {
        $empresas = empresa::all();
        $user = new user();
        $jefes = user::where('es_jefe', 1)->get();

        return view('seguridad.usuario.profile')->with(compact('empresas', 'user', 'jefes'));
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

    public function grupal(Request $request)
    {
        try {
            $empresas_id = $request->input('empresas_id');
            $grupal = grupal::v_grupal($empresas_id)->get();
            $response = ['data' => $grupal];
        } catch (\Exception $exception) {
            return response()->json(['message' => 'There was an error retrieving the records'], 500);
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
}
