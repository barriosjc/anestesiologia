<?php

namespace App\Http\Controllers\produccion;

use App\Http\Controllers\Controller;
use App\Models\Parte_cab;
use Illuminate\Http\Request;

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

}
