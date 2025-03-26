<?php

namespace App\Http\Controllers\entidades;

use App\Models\Valores;
use App\Models\nomenclador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\nomValoresRequest;
use App\Models\NomPracticasEstudio;
use Exception;

class PreciosValoresController extends Controller
{
    public function index()
    {
        $valores = Valores::withTrashed()
            ->paginate();
        $niveles = Nomenclador::select('nivel')->distinct()->get();
        $nivel = null;

        return view("entidades.valores.index", compact("valores", "niveles", "nivel"))
            ->with('i', (request()->input('page', 1) - 1) * $valores->perPage());
    }

    public function guardarGrupo(Request $request)
    {
        $validate = $request->validate(
            [
            "grupo_c" => "required|integer|max:999|exists:nom_valores,grupo",
            "grupo_n" => "required|integer|max:999|unique:nom_valores,grupo",
            "porcentaje" => "required|integer|between:-99,100"
            ],
            [
            "grupo_c.required" => "Debe ingresar el Numero entero de grupo que existe para copiar, tomado como plantilla.",
            "grupo_c.integer" => "El grupo debe ser un número entero.",
            "grupo_c.max" => "El grupo no puede ser mayor a 999.",
            "grupo_c.exists" => "El grupo a copiar no se encuentra en ninguna lista de precios.",
            
            "grupo_n.required" => "Debe ingresar el Numero entero de grupo, el nro puede estar siendo usado.",
            "grupo_n.integer" => "El nuevo grupo debe ser un número entero.",
            "grupo_n.max" => "El nuevo grupo no puede ser mayor a 999.",
            "grupo_n.unique" => "El nuevo grupo ya está asignado a una lista de precios existente.",
            
            "porcentaje.required" => "Debe ingresar un valor de % aplicar entre -99 a 100.",
            "porcentaje.integer" => "El porcentaje debe ser un número entero.",
            "porcentaje.between" => "El porcentaje debe estar entre -99 y 100."
            ]
        );

        try {
            $registros = Valores::where('grupo', $request->grupo_c)->get();
            
            foreach ($registros as $registro) {
                Valores::create(
                    [
                    'nivel' => $registro->nivel,
                    'valor' => $registro->valor + ($request->porcentaje * $registro->valor / 100),
                    'grupo' => $request->grupo_n,
                    'aplica_pocent_adic' => $registro->aplica_pocent_adic,
                    'tipo' => $registro->tipo
                    ]
                );
            }

            return redirect()->back();

        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function filtrar(Request $request)
    {
        $niveles = Nomenclador::select('nivel')->distinct()->get();
        $query = Valores::query();

        if ($request->has('grupo')  && !empty($request->grupo)) {
            $query->where('grupo', '=', $request->grupo);
        }
        if ($request->has('nivel')  && !empty($request->nivel)) {
            $query->where('nivel', '=', $request->nivel);
        }

        $valores = $query->orderBy('nivel', 'asc')
            ->orderBy('created_at', 'asc')
            ->withTrashed()
            ->paginate();
        $nivel = $request->nivel;

        return view("entidades.valores.index", compact("valores", "niveles", "nivel"))
            ->with('i', (request()->input('page', 1) - 1) * $valores->perPage())
            ->with('nivel', $request->nivel);
    }

    public function nuevo(Request $request)
    {
        $nom_practicas_estudios = new NomPracticasEstudio;
        $nivel = $request->nivel;

        return view("entidades.valores.create", compact("nom_practicas_estudios", "nivel"));
    }

    // modifica solo el valor (precio) de un registro
    public function modificar(Request $request)
    {
        $validate = $request->validate([
            "valor" => "required",
        ]);

        $valores = Valores::where("id", $request->valores_id)->first();
        $valores->valor = $this->getValor($request->valor);
        $valores->save();

        return redirect()->back();
    }

    public function guardar(nomValoresRequest $request)
    {
        $validate = $request->validate->validate($request->rulesForStoreOne());

        $validate["valor"] = $this->getValor($request->valor);

        Valores::create($validate);
        // Valores::create([
        //     "nivel" => request()->nivel,
        //     "valor" => $valor_convertido,
        //     "grupo" => request()->grupo,
        //     "aplica_pocent_adic" => request()->aplica_pocent_adic,
        //     "tipo" => request()->tipo
        // ]);

        return redirect()->back()->with('success', 'La operación se ha completado exitosamente.');
    }

    public function borrar(int $id)
    {
        $valores = Valores::find($id);
        $valores->delete();

        return redirect()->back();
    }

    private function getValor($valor)
    {
        if (strpos($valor, ',') !== false) {
            // Formato europeo: eliminar puntos y reemplazar la coma por un punto
            $valor_convertido = str_replace('.', '', $valor);
            $valor_convertido = str_replace(',', '.', $valor_convertido);
        } else {
            // Formato estándar: no hacer nada
            $valor_convertido = $valor;
        }
        if (!is_numeric($valor_convertido)) {
            return redirect()->back()->withErrors(["error" => "Debe ingresar un número valido."]);
        }

        return $valor_convertido;
    }
}
