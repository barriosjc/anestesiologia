<?php

namespace App\Repositories;

use App\Models\Consumo;
use App\Models\Valores;
use App\Models\Cobertura;
use App\Models\ParteCab;
use App\Models\ValoresCab;

class ConsumoRepository
{
    public function valorBuscar(string $periodo, string $nivel, ParteCab $parte_cab)
    {
        $valores = ValoresCab::vValores(
            $parte_cab->gerenciadora_id,
            $parte_cab->cobertura_id,
            $parte_cab->centro_id,
            $periodo,
            $nivel
        );

        return $valores;
    }

    public function parteBuscar(int $id)
    {
        $parte_cab = ParteCab::where("id", $id)->first();

        return $parte_cab;
    }

    public function coberturaBuscar(int $id)
    {
        $cobertura = Cobertura::where("id", $id)->first();

        return $cobertura;
    }
}
