<?php

namespace App\Http\Controllers\entidades;

use App\Models\Valores;
use App\Models\nomenclador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Exception;

class PreciosValoresController extends Controller
{
    public function index()
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

        // dd( $request->cobertura_id, $request->centro_id);
        return view("entidades.nomenclador.valores", compact("valores", "niveles"))
            ->with('i', (request()->input('page', 1) - 1) * $valores->perPage())
            ->with('nivel', $request->nivel);
    }

    public function guardar(Request $request)
    {
        $validate = $request->validate([
            "valor" => "required",
        ]);

        if (strpos($request->valor, ',') !== false) {
            // Formato europeo: eliminar puntos y reemplazar la coma por un punto
            $valor_convertido = str_replace('.', '', $request->valor);
            $valor_convertido = str_replace(',', '.', $valor_convertido);
        } else {
            // Formato estándar: no hacer nada
            $valor_convertido = $request->valor;
        }
        if (!is_numeric($valor_convertido)) {
            return redirect()->back()->withErrors(["error" => "Debe ingresar un número valido."]);
        }

        $valores = valores::where("id", $request->valores_id)->first();
        $valores->valor = $valor_convertido;
        $valores->save();

        return redirect()->back();
    }

    public function borrar(int $id)
    {
        $valores = Valores::find($id);
        $valores->delete();

        return redirect()->back();
    }
}
