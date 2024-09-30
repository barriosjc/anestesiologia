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
use App\Models\Valores_cab;
use Exception;

class PreciosListasController extends Controller
{
    public function index(){
        $listas = Valores_cab::with([
                    'gerenciadora:id,nombre',
                    'cobertura:id,sigla',
                    'centro:id,nombre'
                ])
                ->whereHas('gerenciadora')
                ->whereHas('cobertura')
                ->whereHas('centro')
                ->paginate();
        $coberturas = Cobertura::get();

        return view("entidades.nomenclador.listas",compact("listas", "coberturas"))
            ->with('i', (request()->input('page', 1) - 1) * $listas->perPage());
    }

    public function precios(){
        $valores = Valores::withTrashed()
            ->paginate();
        $niveles = Nomenclador::select('nivel')->distinct()->get();
        $nivel = null;

        return view("entidades.nomenclador.valores",compact("valores", "niveles", "nivel"))
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

    public function filtrar(Request $request)
    {
        $niveles = Nomenclador::select('nivel')->distinct()->get();
        $query = Valores::query();

        if ($request->has('grupo')  && !empty($request->grupo) ) {
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
        return view("entidades.nomenclador.listas",compact("valores", "niveles"))
            ->with('i', (request()->input('page', 1) - 1) * $valores->perPage())
            ->with('nivel', $request->nivel);
    }

    public function guardar(Request $request)
    {
        $validate = $request->validate( [
            "cobertura_id"=> "required",
        ]);

        if($request->has("id") && !empty($request->id)){
            $lista = valores_cab::where("id", $request->id)->first();
        } else {
            $lista = new Valores_cab;
        }
        $lista->fill($validate);
        $lista->save();

        return redirect()->back();
    }

    public function borrar(int $id) {
        $valores = Valores_cab::find($id);
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
