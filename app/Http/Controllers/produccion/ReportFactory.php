<?php
namespace App\Http\Controllers\produccion;

use Exception;
use App\Http\Controllers\produccion\ReportTypeProfxCentro;
use App\Http\Controllers\produccion\ReportTypeDetallexProfesional;

class ReportFactory
{
    public static function create($reporteId)
    {
        switch ($reporteId) {
            case 1:
                return new ReportTypeProfxCentro();
            case 2:
                return new ReportTypeDetallexProfesional();
            default:
                throw new Exception('El reporte seleccionado no esta disponible para generar actualmente.');
        }
    }
}