<?php

namespace App\Services;

use App\Models\ValoresCab;
use App\Models\Parametro;

class ValorizacionService
{
    public function getValor(
        int $gerenciadora_id,
        int $cobertura_id,
        int $centro_id,
        string $periodo,
        string $codigo  // nivel de nomenclador O codigo de nom_practicas_estudios
    ) {
        $resu = ValoresCab::query()
            ->select('nv.valor', 'nv.nivel', 'nv.aplica_pocent_adic', 'nv.moneda')
            ->join('nom_valores as nv', 'nv.grupo', 'nom_valores_cab.grupo')
            ->where('nom_valores_cab.gerenciadora_id', $gerenciadora_id)
            ->where('nom_valores_cab.cobertura_id', $cobertura_id)
            ->where('nom_valores_cab.centro_id', $centro_id)
            ->where('nom_valores_cab.periodo', $periodo)
            ->where('nv.nivel', $codigo)
            ->first();

        if ($resu) {
            $moneda = $resu->moneda ?? 'ARS';
            if ($moneda === 'USD') {
                $valDolar = Parametro::where('nombre', 'USD')->value('valor');
                $resu->valor = round($resu->valor * $valDolar, 2);
            }
        }

        return $resu;
    }
}