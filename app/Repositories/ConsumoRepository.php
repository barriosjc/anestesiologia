<?php

namespace App\Repositories;

use App\Models\Consumo;
use App\Models\Valores;
use App\Models\Cobertura;
use App\Models\Parte_cab;
use App\Models\Valores_cab;

class ConsumoRepository
{
    public function filtrar($request)
    {
        $cobertura_id = $request->has('cobertura_id') ? $request->cobertura_id : null;
        $centro_id = $request->has('centro_id') ? $request->centro_id : null;
        $profesional_id = $request->has('profesional_id') ? $request->profesional_id : null;
        $nombre = $request->has('nombre') ? $request->nombre : null;
        $fec_desde = $request->has('submitInputs') ? $request->fec_desde : null;
        $fec_hasta = $request->has('submitInputs') ? $request->fec_hasta : null;
        $estado_id = $request->has('estado_id') ? $request->estado_id : null;
        $fec_desde_adm = $request->has('fec_desde_adm') ? $request->fec_desde_adm : null;
        $fec_hasta_adm = $request->has('fec_hasta_adm') ? $request->fec_hasta_adm : null;
        $nro_parte = $request->has('nro_parte') ? $request->nro_parte : null;

        $query = Parte_cab::vParteCab();
        if (!empty($cobertura_id)) {
            $query->where('cobertura_id', '=', $cobertura_id);
        }
        if (!empty($centro_id)) {
            $query->where('centro_id', '=', $centro_id);
        }
        if (!empty($profesional_id)) {
            $query->where('profesional_id', '=', $profesional_id);
        }
        if (!empty($estado_id)) {
            $query->where('estado_id', '=', $estado_id);
        }
        if (!empty($nombre)) {
            $query->where('paciente', 'like', "%".$nombre."%");
        }
        if (!empty($fec_desde)) {
            $query->where('fec_prestacion_orig', '>=', $fec_desde);
        }
        if (!empty($fec_hasta)) {
            $query->where('fec_prestacion_orig', '<=', $fec_hasta);
        }
        if (!empty($fec_desde_adm)) {
            $query->where('created_at', '>=', $fec_desde_adm);
        }
        if (!empty($fec_hasta_adm)) {
            $query->where('created_at', '<=', $fec_hasta_adm);
        }
        if (!empty($nro_parte)) {
            $query->where('id', $nro_parte);
        }
        $partes = $query->orderBy('created_at', 'asc')
                    ->paginate();

        return $partes;
    }

    public function valorBuscar($request, $parte_cab)
    {
        $valores = Valores_cab::vValores(
            1,
            $parte_cab->cobertura_id,
            $parte_cab->centro_id,
            $request->periodo,
            $request->nivel
        );

        return $valores;
    }

    public function parteBuscar($id)
    {
        $parte_cab = Parte_cab::where("id", $id)->first();

        return $parte_cab;
    }

    public function coberturaBuscar($id)
    {
        $cobertura = Cobertura::where("id", $id)->first();

        return $cobertura;
    }
}
