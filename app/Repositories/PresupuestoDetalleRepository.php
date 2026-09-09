<?php

namespace App\Repositories;

use App\Models\PresupuestoDet;
<<<<<<< HEAD
use App\Models\Valores_cab;
=======
use App\Models\ValoresCab;
>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856
use Illuminate\Support\Collection;

class PresupuestoDetalleRepository
{
    public function buscarValor(int $gerenciadoraId, int $coberturaId, int $centroId, string $periodo, ?string $nivel)
    {
        return ValoresCab::query()
            ->select(['nv.valor', 'nv.nivel', 'nv.moneda'])
            ->join('nom_valores as nv', 'nv.grupo', '=', 'nom_valores_cab.grupo')
            ->where('nom_valores_cab.gerenciadora_id', $gerenciadoraId)
            ->where('nom_valores_cab.cobertura_id', $coberturaId)
            ->where('nom_valores_cab.centro_id', $centroId)
            ->where('nom_valores_cab.periodo', $periodo)
            ->where('nv.nivel', $nivel)
            ->first();
    }

    public function detalleConDescripcion(int $presupuestoCabId): Collection
    {
        $query1 = PresupuestoDet::query()
            ->join('nom_practicas_estudios as pe', 'presupuestos_det.nom_padre_id', '=', 'pe.nom_padre_id')
            ->whereColumn('presupuestos_det.nomenclador_id', 'pe.id')
            ->where('presupuestos_det.presupuesto_cab_id', $presupuestoCabId)
            ->select('presupuestos_det.*', 'pe.nombre as descripcion');

        $query2 = PresupuestoDet::query()
            ->join('nomenclador as n', 'presupuestos_det.nom_padre_id', '=', 'n.nom_padre_id')
            ->whereColumn('presupuestos_det.nomenclador_id', 'n.id')
            ->where('presupuestos_det.presupuesto_cab_id', $presupuestoCabId)
            ->select('presupuestos_det.*', 'n.descripcion as descripcion');

        return $query1->unionAll($query2)->get();
    }
}
