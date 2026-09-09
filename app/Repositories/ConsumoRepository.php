<?php

namespace App\Repositories;

use App\Models\Consumo;
use App\Models\Valores;
use App\Models\Cobertura;
use App\Models\ParteCab;
use App\Models\ValoresCab;

class ConsumoRepository
{
<<<<<<< HEAD
    public function valorBuscar(string $periodo, string $nivel, Parte_cab $parte_cab)
=======
    public function valorBuscar(string $periodo, string $nivel, ParteCab $parte_cab)
>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856
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
