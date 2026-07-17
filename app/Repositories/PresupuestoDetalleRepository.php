<?php

namespace App\Repositories;

use App\Models\PresupuestoDet;

class PresupuestoDetalleRepository
{
    public function detalleConDescripcion(int $presupuestoCabId)
    {
        $query1 = PresupuestoDet::query()
            ->join('nom_practicas_estudios as pe', function ($join) {
                $join->on('presupuestos_det.nom_padre_id', '=', 'pe.nom_padre_id')
                    ->on('presupuestos_det.nomenclador_id', '=', 'pe.id');
            })
            ->where('presupuestos_det.presupuesto_cab_id', $presupuestoCabId)
            ->select('presupuestos_det.*', 'pe.nombre as descripcion');

        $query2 = PresupuestoDet::query()
            ->join('nomenclador as n', function ($join) {
                $join->on('presupuestos_det.nom_padre_id', '=', 'n.nom_padre_id')
                    ->on('presupuestos_det.nomenclador_id', '=', 'n.id');
            })
            ->where('presupuestos_det.presupuesto_cab_id', $presupuestoCabId)
            ->select('presupuestos_det.*', 'n.descripcion as descripcion');

        return $query1->unionAll($query2)->get();
    }
}
