<?php

namespace App\Http\Controllers\entidades;

use Exception;
use App\Models\Centro;
use App\Models\Periodo;
use App\Models\Valores;
use App\Models\Cobertura;
use App\Models\Nomenclador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\NomencladoresServices;

class NomencladorController extends Controller
{
    public function listas()
    {
        $valores = Valores::withTrashed()
            ->paginate();
        $niveles = Nomenclador::select('nivel')->distinct()->get();
        $nivel = null;

        return view("entidades.valores.index", compact("valores", "niveles", "nivel"))
            ->with('i', (request()->input('page', 1) - 1) * $valores->perPage());
    }

    public function precios()
    {
        $valores = Valores::withTrashed()
            ->paginate();
        $niveles = Nomenclador::select('nivel')->distinct()->get();
        $nivel = null;

        return view("entidades.valores.index", compact("valores", "niveles", "nivel"))
            ->with('i', (request()->input('page', 1) - 1) * $valores->perPage());
    }
    
    public function valoresNuevos(Request $request)
    {
        $validate = $request->validate([
            "cobertura_id"=> "required",
            "perido" => "required"
        ]);

        try {
            $niveles = valores::where("cobertura_id", $request->cobertura_id)
                                ->where("periodo", $request->perido)
                                ->exists();
            if ($niveles) {
                throw new Exception("Error, Ya hay valores cargados para el Convenio y Centro seleccionado, no se puede duplicar.");
            }

            if (isset($request->cobertura_id_copy) || isset($request->centro_id_copy)) {
                // Recoger los datos basados en los parámetros
                $datosOriginales = Valores::where('cobertura_id', $request->cobertura_id_copy)
                                        ->where('centro_id', $request->centro_id_copy)
                                        ->select('nivel', 'valor')
                                        ->withTrashed()
                                        ->get();

                // Mapear los datos para cambiar cobertura_id y centro_id
                $datosModificados = $datosOriginales->map(function ($item) use ($request) {
                    return [
                        'cobertura_id' => $request->cobertura_id, // Nuevo valor de cobertura_id
                        'centro_id' => $request->centro_id,       // Nuevo valor de centro_id
                        'nivel' => $item->nivel,
                        'valor' => $item->valor,
                    ];
                })->toArray();

                // Insertar los datos modificados en la tabla
                DB::table('nom_valores')->insert($datosModificados);
            } else {
                $nivelesQuery = Nomenclador::selectRaw(
                    '? as cobertura_id, ? as centro_id, nivel',
                    [$request->cobertura_id ?? null, $request->centro_id ?? null]
                )
                                        ->distinct();
                DB::table('nom_valores')->insertUsing(['cobertura_id', 'centro_id', 'nivel'], $nivelesQuery);
            }
            $valores = valores::where("cobertura_id", $request->cobertura_id ?? null)
                        ->where("centro_id", $request->centro_id ?? null)
                        ->paginate();
            $coberturas = Cobertura::orderby("nombre")->get();
            $centros = Centro::orderby("nombre")->get();

            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function valoresFiltrar(Request $request)
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
        return view("entidades.valores.index", compact("valores", "niveles"))
            ->with('i', (request()->input('page', 1) - 1) * $valores->perPage())
            ->with('nivel', $request->nivel);
    }

    // public function valor_guardar(Request $request) //si se usa pasar a camelCase
    // {
    //     $validate = $request->validate([
    //         "valor"=> "required",
    //     ]);

    //     if (strpos($request->valor, ',') !== false) {
    //         // Formato europeo: eliminar puntos y reemplazar la coma por un punto
    //         $valor_convertido = str_replace('.', '', $request->valor);
    //         $valor_convertido = str_replace(',', '.', $valor_convertido);
    //     } else {
    //         // Formato estándar: no hacer nada
    //         $valor_convertido = $request->valor;
    //     }
    //     if (!is_numeric($valor_convertido)) {
    //         return redirect()->back()->withErrors(["error" => "Debe ingresar un número valido."]);
    //     }

    //     $valores = valores::where("id", $request->valores_id)->first();
    //     $valores->valor = $valor_convertido;
    //     $valores->save();

    //     return redirect()->back();
    // }

    public function valoresBorrar(int $id)
    {
        $valores = Valores::find($id);
        $valores->delete();

        return redirect()->back();
    }

    public function buscarCodDesc(Request $request, NomencladoresServices $nomencladoresServices)
    {
        $texto = $request->input('descripcion');
        if ($request->input('codigo') != null) {
            $texto = $request->input('codigo');
        }
        $nom_padre_json = $request->input('nom_padre_json');
        $results = $nomencladoresServices->buscar($texto, $nom_padre_json);

        return response()->json($results);

    }
}
