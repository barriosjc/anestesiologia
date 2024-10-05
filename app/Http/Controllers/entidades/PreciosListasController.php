<?php

namespace App\Http\Controllers\entidades;

use Exception;
use App\Models\Centro;
use App\Models\Periodo;
use App\Models\Valores;
use App\Models\Cobertura;
use App\Models\nomenclador;
use App\Models\Valores_cab;
use App\Models\Gerenciadora;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PreciosListasController extends Controller
{
    public function index()
    {
        $listas = Valores_cab::with([
            'gerenciadora:id,nombre',
            'cobertura:id,sigla',
            'centro:id,nombre'
        ])
            ->whereHas('gerenciadora')
            ->whereHas('cobertura')
            ->whereHas('centro')
            ->paginate();
        $gerenciadoras = Gerenciadora::orderby("nombre")->get();
        $coberturas = Cobertura::orderby("nombre")->get();
        $centros = Centro::orderby("nombre")->get();
        $periodos = Periodo::orderby("nombre")->get();
        $validated = ["gerenciadora_id" => null, "cobertura_id" => null,
        "centro_id" => null, "periodo" => null, "grupo" => null];

        return view("entidades.nomenclador.listas", compact("listas", "gerenciadoras", "coberturas", "centros", "periodos", "validated"))
            ->with('i', (request()->input('page', 1) - 1) * $listas->perPage());
    }

    public function precios()
    {
        $valores = Valores::withTrashed()
            ->paginate();
        $niveles = Nomenclador::select('nivel')->distinct()->get();
        $nivel = null;

        return view("entidades.nomenclador.valores", compact("valores", "niveles", "nivel"))
            ->with('i', (request()->input('page', 1) - 1) * $valores->perPage());
    }

    public function nuevo(Request $request)
    {
        $gerenciadoras = Gerenciadora::orderby("nombre")->get();
        $coberturas = Cobertura::orderby("nombre")->get();
        $centros = Centro::orderby("nombre")->get();
        $periodos = Periodo::orderby("nombre")->get();
        $listas = new Valores_cab;

        return view("entidades.nomenclador.create", compact("gerenciadoras", "coberturas", "centros", "periodos", "listas"));
    }

    public function modificar(int $id)
    {
        $gerenciadoras = Gerenciadora::orderby("nombre")->get();
        $coberturas = Cobertura::orderby("nombre")->get();
        $centros = Centro::orderby("nombre")->get();
        $periodos = Periodo::orderby("nombre")->get();
        $listas = Valores_cab::where("id", $id)->first();
// dd($listas);
        return view("entidades.nomenclador.edit", compact("gerenciadoras", "coberturas", "centros", "periodos", "listas"));
    }

    public function filtrar(Request $request,)
    {
        $validated = $request->validate([
            'gerenciadora_id' => 'nullable|integer|exists:gerenciadoras,id',
            'cobertura_id' => 'nullable|integer|exists:coberturas,id',
            'centro_id' => 'nullable|integer|exists:centros,id',
            'periodo' => 'nullable|string|exists:periodos,nombre',
            'grupo' => 'nullable|integer|min:1|max:1000'
        ]);

        $query = Valores_cab::with([
            'gerenciadora:id,nombre',
            'cobertura:id,sigla',
            'centro:id,nombre'
        ])
            ->whereHas('gerenciadora')
            ->whereHas('cobertura')
            ->whereHas('centro');

        if ($request->has('gerenciadora_id')  && !empty($request->gerenciadora_id)) {
            $query->where('gerenciadora_id', '=', $request->gerenciadora_id);
        }
        if ($request->has('cobertura_id')  && !empty($request->cobertura_id)) {
            $query->where('cobertura_id', '=', $request->cobertura_id);
        }
        if ($request->has('centro_id')  && !empty($request->centro_id)) {
            $query->where('centro_id', '=', $request->centro_id);
        }
        if ($request->has('periodo')  && !empty($request->periodo)) {
            $query->where('periodo', '=', $request->periodo);
        }
        if ($request->has('grupo')  && !empty($request->grupo)) {
            $query->where('grupo', '=', $request->grupo);
        }
        $listas = $query->paginate();
        $gerenciadoras = Gerenciadora::orderby("nombre")->get();
        $coberturas = Cobertura::orderby("nombre")->get();
        $centros = Centro::orderby("nombre")->get();
        $periodos = Periodo::orderby("nombre")->get();


        return view("entidades.nomenclador.listas", compact("listas", "gerenciadoras", "coberturas", "centros", "periodos", "validated"))
            ->with('i', (request()->input('page', 1) - 1) * $listas->perPage());
    }

    public function guardar(Request $request)
    {
        $validated = $request->validate(
            [
            'cobertura_id' => 'required|integer',
            'centro_id' => 'required|integer',
            'periodo' => 'required|string|max:10',
            'grupo' => 'required|integer|min:1|max:1000',
            'gerenciadora_id' => [
                'required',
                'integer',
                Rule::unique('nom_valores_cab')
                    ->where(function ($query) use ($request) {
                        return $query->where('cobertura_id', $request->cobertura_id)
                                    ->where('centro_id', $request->centro_id)
                                    ->where('periodo', $request->periodo);
                    })
                    ->ignore($request->id)
            ]
            ],
            ['gerenciadora_id.unique' => 'Los datos ingresados para la lista de precios ya tiene un grupo asignado.'
            ]
        );

        if ($request->has("id") && !empty($request->id)) {
            $lista = valores_cab::where("id", $request->id)->first();
        } else {
            $lista = new Valores_cab;
        }
        $lista->fill($validated);
        $lista->save();

        return redirect()->back();
    }

    public function borrar(int $id)
    {
        $valores = Valores_cab::find($id);
        $valores->delete();

        return redirect()->back();
    }
}
