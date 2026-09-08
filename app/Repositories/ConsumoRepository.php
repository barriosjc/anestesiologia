<?php

namespace App\Repositories;

use App\Models\Consumo;
use App\Models\Valores;
use App\Models\Cobertura;
use App\Models\Parte_cab;
use App\Models\Valores_cab;

class ConsumoRepository
{
    public function valorBuscar(string $periodo, string $nivel, Parte_cab $parte_cab)
    {
        $valores = Valores_cab::vValores(
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
        $parte_cab = Parte_cab::where("id", $id)->first();

        return $parte_cab;
    }

    public function coberturaBuscar(int $id)
    {
        $cobertura = Cobertura::where("id", $id)->first();

        return $cobertura;
    }
}
