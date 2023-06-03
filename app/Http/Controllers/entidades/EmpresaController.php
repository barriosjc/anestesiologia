<?php

namespace App\Http\Controllers\entidades;

use App\Models\Empresa;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

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
        $empresas = Empresa::paginate();
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
            'uri' => 'required|string|max:45',
            'logo' => 'required|string|max:45',
            'perfil_id' => 'required'
        ], [
            "uri.required" => 'El prefijo es obligatrio, es el dato que se indica cuando se ejecuta dominio con empresa  (www.ejemplo.com/portal/prefijo)',
            "perfil_id.required" => "Debe seleccionar al menos un perfil, los perfiles que seleccione se van a poder seleccionar a los usuarios",
        ]);

        request()->validate(Empresa::$rules);

        $empresa = Empresa::create($request->all());

        if (isset($request->perfil_id)) {
            foreach ($request->perfil_id as $key => $value) {
                DB::table('roles_empresas')
                    ->insert([
                        'roles_id' => $value,
                        'empresas_id' => $empresa->id
                    ]);;
            }
        }

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
            'uri' => 'required|string|max:45',
            'logo' => 'required|string|max:45',
            'perfil_id' => 'required'
        ], [
            "uri.required" => 'El prefijo es obligatrio, es el dato que se indica cuando se ejecuta dominio con empresa  (www.ejemplo.com/portal/prefijo)',
            "perfil_id.required" => "Debe seleccionar al menos un perfil, los perfiles que seleccione se van a poder seleccionar a los usuarios",
        ]);

        $empresa->razon_social = $request->razon_social;
        $empresa->contacto = $request->contacto;
        $empresa->telefono = $request->telefono;
        $empresa->uri = $request->uri;
        $empresa->logo = $request->logo;
        $empresa->save();

        DB::delete("delete from roles_empresas where empresas_id = ?", array($empresa->id));    
        //asigna los roles marcados
        if (isset($request->perfil_id)) {
            foreach ($request->perfil_id as $key => $value) {
                DB::insert('insert into roles_empresas (roles_id, empresas_id) values (?, ?)', array($value, $empresa->id));
            }
        }

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
}
