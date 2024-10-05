<?php

namespace App\Http\Controllers\produccion;

use App\Enums\Orientacion;
use App\Enums\TamanoPapel;

class PdfFormat
{
    private TamanoPapel $tamano;  // Cambiado de string a TamanoPapel
    private Orientacion $orientacion;

    public function __construct(TamanoPapel $tamano, Orientacion $orientacion)
    {
        $this->tamano = $tamano;
        $this->orientacion = $orientacion;
    }

    public function getTamano(): string
    {
        return $this->tamano->value;  // Devuelve el valor del enum
    }

    public function getOrientacion(): string
    {
        return $this->orientacion->value;  // Devuelve el valor del enum
    }
}
