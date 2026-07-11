<?php

namespace App\Http\Controllers\produccion;

use App\Http\Controllers\Controller;
use App\Http\Controllers\produccion\ReportFactory;
use App\Models\Centro;
use App\Models\Cobertura;
use App\Models\Estado;
use App\Models\Listado;
use App\Models\Parametro;
use App\Models\Parte_cab;
use App\Models\Periodo;
use App\Models\Profesional;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsumoController extends Controller
{
    public function observar(Request $request)
    {
        $validate = $request->validate([
            "id" => "required",
            "observaciones" => "required|max:255",
            "estado_cambio" => "required"
        ]);

        $result = $this->cambiaEstado($validate);
    
        if ($result['success']) {
            return redirect()->route("consumos.partes.filtrar")->with('success', 'Estado cambiado exitosamente.');
        } else {
            return redirect()->back()->withErrors($result['message'])->withInput();
        }
    }
    
    public function aProcesar(Request $request)
    {
        $validate = $request->validate([
            "id" => "required",
            "estado_cambio" => "required",
            "observaciones" => "nullable|max:255"
        ], [
            "estado_cambio.required" => "¡Atención! La selección del estado es obligatoria."
        ]);
        
        $result = $this->cambiaEstado($validate);
    
        if ($result['success']) {
            return redirect()->route("partes_cab.filtrar")->with('success', 'Estado cambiado exitosamente.');
        } else {
            return redirect()->back()->withErrors($result['message'])->withInput();
        }
    }
    
    private function cambiaEstado($ingresos)
    {
        try {
            $parte = parte_cab::find($ingresos["id"]);
            // if (!($parte->estado_id == 1 || $parte->estado_id == 2 || $parte->estado_id == 9) && $ingresos['estado_cambio'] == 3) {
            //     throw new \Exception('El estado no puede ser cambiado a "A liquidar" desde el estado actual.');
            // }
            // if (!($parte->estado_id == 1 || $parte->estado_id == 3 || $parte->estado_id == 4) && $ingresos['estado_cambio'] == 2) {
            //     throw new \Exception('El estado no puede ser cambiado a "Observado" desde el estado actual.');
            // }
            // if (!($parte->estado_id == 1 || $parte->estado_id == 2 || $parte->estado_id == 4) && $ingresos['estado_cambio'] == 9) {
            //     throw new \Exception('El estado no puede ser cambiado a "Con faltantes" desde el estado actual.');
            // }

            $parte->observaciones = strip_tags($ingresos['observaciones']);
            $parte->estado_id = $ingresos['estado_cambio'];
            $parte->save();
    
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function rendicionListado(Request $request)
    {
        $coberturas = Cobertura::orderby("nombre")->get();
        $centros = Centro::orderby("nombre")->get();
        $profesionales = Profesional::get();
        $estados = Estado::get();
        $periodos = Periodo::orderby("nombre")->get();
        $role_id = Parametro::where('nombre', 'listado_presupuesto')->first()->valor;
        $user = Auth::user();
        $listados = Listado::when(!$user->hasRole('super-admin'), function ($query) use ($user) {
                $query->whereIn('role_id', $user->roles->pluck('id'));
            })
            ->get();
        $estadosPresupuesto = [
                        'I' => 'Ingresado',
                        'P' => 'Pagadkso',
                        'C' => 'Cancelado',
                        'O' => 'Cobrado',
                        'F' => 'Facturado',
                    ];
        $users = User::get();
        return view("consumo.listados", compact("users", "periodos", "coberturas", "centros", "profesionales", "estados", "listados", "estadosPresupuesto"));
    }

    public function rendicionListar(Request $request)
    {
        try {
            if (!$request->reporte_id) {
                throw new Exception('Es obligatorio seleccionar el tipo de reporte a generar.');
            }
    
            // Crear la estrategia adecuada usando la Factory
            $strategy = ReportFactory::create($request->reporte_id);
          
            // Validar la solicitud usando la estrategia
            $par_adicionales = $strategy->validate($request);

            // Generar el reporte
            $reporte = $strategy->generate($request);
    
            if (count($reporte) == 0) {
                throw new Exception('Atención !!! No se ha encontrado datos para generar la Rendición.');
            }

            if ($request->reporte_id == 2) {  
                $conObs = $reporte->contains(function ($item) {
                    return in_array($item->estado_id, [7, 8]);
                });
                $par_adicionales["conObs"] = $conObs;
            }
            $parametros = $par_adicionales;
            $parametros["datos"] = $reporte;
            $parametros["parametros"] = $par_adicionales;

            $viewName = $strategy->getViewName();
            $formato = $strategy->getFormat();
            $pdf = Pdf::loadView($viewName, $parametros)
                        ->setPaper($formato->getTamano(), $formato->getOrientacion());

            return $pdf->stream();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator->errors())->withInput();
        } catch (\Exception $e) {
            return back()->withErrors([$e->getMessage()])->withInput();
        }
    }

}
