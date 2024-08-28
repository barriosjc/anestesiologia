<?php

namespace App\Http\Controllers\entidades;

use App\Models\Centro;
use App\Models\Valores;
use App\Models\Cobertura;
use App\Models\nomenclador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Periodo;
use Exception;

class NomencladorController extends Controller
{
    public function valores(){
        $valores = Valores::withTrashed()
        ->paginate();
        $coberturas = Cobertura::get();
        $periodos = Periodo::get();
        $cobertura_id = null;
        $periodo = null;

        return view("entidades.nomenclador.valores",compact("valores", "coberturas", "periodos", "cobertura_id", "periodo"))
            ->with('i', (request()->input('page', 1) - 1) * $valores->perPage());
    }

    public function valores_nuevos(Request $request)
    {
        $validate = $request->validate( [
            "cobertura_id"=> "required",
            "perido" => "required"
        ]);

        try{
            $niveles = valores::where("cobertura_id", $request->cobertura_id)
                                ->where("periodo", $request->perido)
                                ->exists();
            if($niveles){
                throw new Exception("Error, Ya hay valores cargados para el Convenio y Centro seleccionado, no se puede duplicar.");
            }

            if(isset($request->cobertura_id_copy) || isset($request->centro_id_copy)){
                // Recoger los datos basados en los parámetros
                $datosOriginales = Valores::where('cobertura_id', $request->cobertura_id_copy)
                                        ->where('centro_id', $request->centro_id_copy)
                                        ->select('nivel', 'valor')
                                        ->withTrashed()
                                        ->get();

                // Mapear los datos para cambiar cobertura_id y centro_id
                $datosModificados = $datosOriginales->map(function($item) use ($request) {
                    return [
                        'cobertura_id' => $request->cobertura_id, // Nuevo valor de cobertura_id
                        'centro_id' => $request->centro_id,       // Nuevo valor de centro_id
                        'nivel' => $item->nivel,
                        'valor' => $item->valor,
                    ];
                })->toArray();

                // Insertar los datos modificados en la tabla
                DB::table('nom_valores')->insert($datosModificados);
            }else
            {  
                $nivelesQuery = Nomenclador::selectRaw('? as cobertura_id, ? as centro_id, nivel', 
                                                        [$request->cobertura_id ?? null, $request->centro_id ?? null])
                                        ->distinct();
                DB::table('nom_valores')->insertUsing(['cobertura_id', 'centro_id', 'nivel'], $nivelesQuery);
            }
            $valores = valores::where("cobertura_id", $request->cobertura_id ?? null)
                        ->where("centro_id", $request->centro_id ?? null)
                        ->paginate();
            $coberturas = Cobertura::get();
            $centros = Centro::get();

            return redirect()->back();

        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function valores_filtrar(Request $request)
    {
        $coberturas = Cobertura::get();
        $centros = Centro::get();
        $query = Valores::query();

        // if ($request->has('cobertura_id')  && !empty($request->cobertura_id) ) {
            $query->where('grupo', '=', $request->grupo);
        // }
        // if ($request->has('centro_id')  && !empty($request->centro_id)) {
            // $query->where('centro_id', '=', $request->centro_id);
        // }

        $valores = $query->orderBy('nivel', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->withTrashed()
                    ->paginate();

// dd( $request->cobertura_id, $request->centro_id);  
        return view("entidades.nomenclador.valores",compact("valores", "coberturas", "centros"))
            ->with('i', (request()->input('page', 1) - 1) * $valores->perPage())
            ->with('cobertura_id', $request->cobertura_id)
            ->with('centro_id', $request->centro_id);

    }

    public function valor_guardar(Request $request)
    {
        $validate = $request->validate( [
            "valor"=> "required",
        ]);

        $valores = valores::where("id", $request->valores_id)->update(["valor" => $request->valor]);

        return redirect()->back();
    }

    public function valores_borrar(int $id) {
        $valores = Valores::find($id);  
        $valores->delete();

        return redirect()->back();  
    }

    public function valores_buscar(Request $request) 
    {
        $codigo = str_replace("-", "", $request->input('codigo'));
        $descripcion = $request->input('descripcion');

        $query = Nomenclador::query();
        if ($codigo) {
            $query->where(DB::raw('REPLACE(codigo, "-", "")'),'like', '%' . $codigo . '%');
        }

        if ($descripcion) {
            $query->where('descripcion', 'like', '%' . $descripcion . '%');
        }

        $results = $query->get();
        // $sql = $query->toSql();
        // $bindings = $query->getBindings();
        // dd($sql,$bindings);
        return response()->json($results);
    }

}
