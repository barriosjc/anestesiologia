<?php

namespace App\Services\Reports;

use App\Enums\Orientacion;
use App\Enums\TamanoPapel;

class PdfFormat
{
    private TamanoPapel $tamano;
    private Orientacion $orientacion;

    public function __construct(TamanoPapel $tamano, Orientacion $orientacion)
    {
        $this->tamano = $tamano;
        $this->orientacion = $orientacion;
    }

    public function getTamano(): string
    {
        return $this->tamano->value;
    }

    public function getOrientacion(): string
    {
        return $this->orientacion->value;
    }
}
