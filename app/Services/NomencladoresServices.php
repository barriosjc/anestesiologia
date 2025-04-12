<?php

namespace App\Services;

use App\Models\Nomenclador;
use App\Models\Gerenciadora;
use App\Models\PracticasEstudios;
use App\Models\NomPracticasEstudio;

class NomencladoresServices
{
    public function buscar(string $termino, int $gerenciadora_id)
    {
        $nom_padres = Gerenciadora::find($gerenciadora_id)->nomPadres()->pluck('id')->toArray();

        // Nomenclador: nivel, descripcion
        $nomencladores = Nomenclador::where(function($query) use ($termino) {
            $query->where('nivel', 'like', "%{$termino}%")
                  ->orWhere('codigo', 'like', "%{$termino}%")
                  ->orWhere('descripcion', 'like', "%{$termino}%");
        })
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
        $practicas = NomPracticasEstudio::where(function($query) use ($termino) {
            $query->where('codigo', 'like', "%{$termino}%")
                  ->orWhere('nombre', 'like', "%{$termino}%");
        })
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