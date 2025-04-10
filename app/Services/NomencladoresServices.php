<?php

namespace App\Services;

use App\Models\Cobertura;
use App\Models\Nomenclador;
use App\Models\NomPracticasEstudio;
use App\Models\PracticasEstudios;

class NomencladoresServices
{
    public function buscar(string $termino, int $cobertura_id)
    {
        $nom_padres = Cobertura::find($cobertura_id)->nomPadres()->pluck('id')->toArray();

        // Nomenclador: nivel, descripcion
        $nomencladores = Nomenclador::where('nivel', 'like', "%{$termino}%")
            ->orWhere('descripcion', 'like', "%{$termino}%")
            ->whereIn('nom_padre_id', $nom_padres)
            ->get()
            ->map(function ($nomenclador) {
                return [
                    'id' => $nomenclador->id,
                    'codigo' => $nomenclador->codigo,
                    'nivel' => $nomenclador->nivel,
                    'descripcion' => $nomenclador->descripcion,
                    'nom_padre_id' => $nomenclador->nom_padre_id,
                ];
            });

        // PracticasEstudios: codigo, nombre
        $practicas = NomPracticasEstudio::where('codigo', 'like', "%{$termino}%")
            ->orWhere('nombre', 'like', "%{$termino}%")
            ->whereIn('nom_padre_id', $nom_padres)
            ->get()
            ->map(function ($practica) {
                return [
                    'id' => $practica->id,
                    'codigo' => $practica->codigo,
                    'nivel' => null,
                    'descripcion' => $practica->nombre,
                    'nom_padre_id' => $practica->nom_padre_id,
                ];
            });

        // Combinar resultados
        return $nomencladores->concat($practicas)->sortBy('descripcion')->values()->all();
    }
}