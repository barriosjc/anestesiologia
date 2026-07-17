<?php

namespace App\Services\Reports;

use Exception;

class ReportFactory
{
    public static function create($reporteId)
    {
        switch ($reporteId) {
            case 1:
                return new ReportTypeProfxCentro();
            case 2:
                return new ReportTypeDetallexProfesional();
            case 3:
                return new ReportTypeProduccionAdministrativos();
            case 4:
                return new ReportTypePresupuestoEstados();
            default:
                throw new Exception('El reporte seleccionado no esta disponible para generar actualmente.');
        }
    }
}
