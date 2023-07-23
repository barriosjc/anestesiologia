<?php

namespace App\Http\Controllers\entidades;

use App\Models\Empresa;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Class EmpresaController
 * @package App\Http\Controllers
 */
class EmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $empresas = Empresa::simplepaginate();
        $perfiles = role::all();

        return view('empresa.index', compact('empresas', 'perfiles'))
            ->with('i', (request()->input('page', 1) - 1) * $empresas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $empresa = new Empresa();
        $perfiles = role::all();
        $roles_empresas = [];

        return view('empresa.create', compact('empresa', 'perfiles', 'roles_empresas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'razon_social' => 'required|string|max:200',
            'contacto' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:45',
            'uri' => 'required|regex:/^[a-zA-Z0-9-]+$/|max:45',
            'logo' => 'required',
            'login' => 'required',
            'listado_reconocimientos' => 'required',
            'emitir_reconocimiento' => 'required',            
            'email_contacto' => 'required|email|max:100',
            'email_nombre' => 'required|max:100',
        ], [
            "uri.required" => 'El prefijo es obligatrio, es el dato que se indica cuando se ejecuta dominio con empresa  (www.ejemplo.com/portal/prefijo)',
            'uri.regex' => 'El atributo Prefijo solo puede contener texto, números o guiones.',
        ]);

        $empresa = new Empresa();
        foreach ($validated as $key => $value) {
            $empresa->$key = $value;
        }

        //$opciones->empresas_id = session('empresa')->id;
        $originalName = $request->file('logo')->getClientOriginalName();
        $path = $empresa['uri'] . "/imagenes/" . $originalName;
        Storage::disk('empresas')->put($path, $request->file('logo')->get());
        $empresa->logo = $path;

        $originalName = $request->file('login')->getClientOriginalName();
        $path = $empresa['uri'] . "/imagenes/" . $originalName;
        Storage::disk('empresas')->put($path, $request->file('login')->get());
        $empresa->login = $path;

        $originalName = $request->file('listado_reconocimientos')->getClientOriginalName();
        $path = $empresa['uri'] . "/imagenes/" . $originalName;
        Storage::disk('empresas')->put($path, $request->file('listado_reconocimientos')->get());
        $empresa->listado_reconocimientos = $path;

        $originalName = $request->file('emitir_reconocimiento')->getClientOriginalName();
        $path = $empresa['uri'] . "/imagenes/" . $originalName;
        Storage::disk('empresas')->put($path, $request->file('emitir_reconocimiento')->get());
        $empresa->emitir_reconocimiento = $path;

        $empresa->save();

        DB::insert("insert into PERMISSIONS (name, guard_name)
                        (select name, '".$validated['uri']."' from PERMISSIONS 
                                    where guard_name = 'web' )
                    ");
        


        return redirect()->route('empresas.index')
            ->with('success', 'Empresa created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $empresa = Empresa::find($id);
        $roles_empresas = role::v_roles_empresas($id)->get();

        return view('empresa.show', compact('empresa', 'roles_empresas'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $empresa = Empresa::find($id);
        $perfiles = role::all();
        $o_roles_empresas = role::v_roles_empresas($id)->get();
        $roles_empresas = [];
        foreach ($o_roles_empresas as $value) {
            $roles_empresas[] += $value->id;
        }

        return view('empresa.edit', compact('empresa', 'perfiles', 'roles_empresas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Empresa $empresa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Empresa $empresa)
    {
        $validated = $request->validate([
            'razon_social' => 'required|string|max:200',
            'contacto' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:45',
            'uri' => 'required|regex:/^[a-zA-Z0-9-]+$/|max:45',
            'logo' => Rule::requiredIf(empty($empresa->logo)), 
            'login' => Rule::requiredIf(empty($empresa->login)), 
            'listado_reconocimientos' => Rule::requiredIf(empty($empresa->listado_reconocimientos)), 
            'emitir_reconocimiento' =>  Rule::requiredIf(empty($empresa->emitir_reconocimiento)),   
            'email_contacto' => 'required|email|max:100',
            'email_nombre' => 'required|max:100',
        ], [
            "uri.required" => 'El prefijo es obligatrio, es el dato que se indica cuando se ejecuta dominio con empresa  (www.ejemplo.com/portal/prefijo)',
            'uri.regex' => 'El atributo Prefijo solo puede contener texto, números o guiones.',
        ]);
        $uri_anterior = $empresa->uri;
        $empresa->razon_social = $request->razon_social;
        $empresa->contacto = $request->contacto;
        $empresa->telefono = $request->telefono;
        $empresa->uri = $request->uri;
        // $empresa->logo = $request->logo;
        $empresa->email_contacto = $request->email_contacto;
        $empresa->email_nombre = $request->email_nombre;

        if ($request->hasFile('logo')) {
            $originalName = $request->file('logo')->getClientOriginalName();
            $path = $empresa['uri'] . "/imagenes/" . $originalName;
            Storage::disk('empresas')->put($path, $request->file('logo')->get());
            $empresa->logo = $path;
        }

        if ($request->hasFile('login')) {
            $originalName = $request->file('login')->getClientOriginalName();
            $path = $empresa['uri'] . "/imagenes/" . $originalName;
            Storage::disk('empresas')->put($path, $request->file('login')->get());
            $empresa->login = $path;
        }

        if ($request->hasFile('listado_reconocimientos')) {
           $originalName = $request->file('listado_reconocimientos')->getClientOriginalName();
            $path = $empresa['uri'] . "/imagenes/" . $originalName;
            Storage::disk('empresas')->put($path, $request->file('listado_reconocimientos')->get());
            $empresa->listado_reconocimientos = $path;
        }
        if ($request->hasFile('emitir_reconocimiento')) {
            $originalName = $request->file('emitir_reconocimiento')->getClientOriginalName();
            $path = $empresa['uri'] . "/imagenes/" . $originalName;
            Storage::disk('empresas')->put($path, $request->file('emitir_reconocimiento')->get());
            $empresa->emitir_reconocimiento = $path;
        }
        $empresa->save();

        DB::delete("delete from PERMISSIONS where guard_name= '".$uri_anterior."'");
        DB::insert("insert into PERMISSIONS (name, guard_name)
                        (select name, '".$validated['uri']."' from PERMISSIONS 
                                    where guard_name = 'web' )
                    ");

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa updated successfully');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $empresa = Empresa::find($id)->delete();

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa deleted successfully');
    }

    public function entorno($entorno)
    {
        $empresa = Empresa::where('uri', $entorno)->first();
        if ($empresa) {
            session(['empresa' => $empresa]);
        } else {
            session()->forget('empresa');
            Auth::logout();
        }

        return redirect()->route('login');
    }

    public function select() {
        $empresas = Empresa::all();

        return view('empresa.select', compact('empresas'));
    }

    public function set(Request $request) {
        $validated = $request->validate([
            'empresas_id' => 'required',

        ], [
            "empresas_id.required" => "Debe seleccionar una empresa para continuar trabajando con el sistema.",
        ]);

        Auth::user()->empresas_id = $validated['empresas_id'];
        $empresa = Empresa::where('id', $validated['empresas_id'])->first();
        session(['empresa' => $empresa]);

        return redirect()->route('main');
    }
}
