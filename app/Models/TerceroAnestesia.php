<?php

namespace App\Models;

use App\Enums\AnestesiaEstado;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerceroAnestesia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'terceros_anestesia';

    protected $fillable = [
        'fecha',
        'hora',
        'paciente',
        'dni',
        'nomenclador_id',
        'nom_padre_id',
        'estado',
        'profesional_id',
        'cobertura_id',
        'urgencia',
        'observaciones',
        'pasado_sistema',
    ];

    protected function casts(): array
    {
        return [
            'fecha'          => 'date',
            'estado'         => AnestesiaEstado::class,
            'urgencia'       => 'boolean',
            'pasado_sistema' => 'boolean',
        ];
    }

    public function nomenclador(): BelongsTo
    {
        return $this->belongsTo(Nomenclador::class, 'nomenclador_id');
    }

    /**
     * Para permitir que nomenclador_id apunte tambien a 'nom_practicas_estudios',
     * que comparte el mismo esquema (id + nom_padre_id) pero es otra tabla con
     * su propio autoincremental.
     */
    public function nomPractica(): BelongsTo
    {
        return $this->belongsTo(NomPracticasEstudio::class, 'nomenclador_id');
    }

    /**
     * Procedimiento seleccionado (Nomenclador o NomPracticasEstudio),
     * normalizado con los mismos atributos para la vista.
     *
     * Se usa nom_padre_id para desambiguar cuando nomenclador_id podria
     * existir en ambas tablas (autoincrementales independientes).
     * Aprovecha las relaciones pre-cargadas (eager loading) en el indice
     * para evitar N+1.
     */
    public function getProcedimientoAttribute(): ?object
    {
        // Usar relaciones pre-cargadas si estan disponibles (evita N+1)
        $nomenclador = $this->relationLoaded('nomenclador') ? $this->nomenclador : null;
        $practica = $this->relationLoaded('nomPractica') ? $this->nomPractica : null;

        // Si no estan pre-cargadas, cargarlas (lazy loading: un query extra)
        if (! $this->relationLoaded('nomenclador')) {
            $nomenclador = Nomenclador::find($this->nomenclador_id);
        }
        if (! $this->relationLoaded('nomPractica')) {
            $practica = NomPracticasEstudio::find($this->nomenclador_id);
        }

        // Con nom_padre_id: desambiguar usando el nom_padre_id real
        if ($this->nom_padre_id) {
            if ($nomenclador && $nomenclador->nom_padre_id == $this->nom_padre_id) {
                return (object) [
                    'id'           => $nomenclador->id,
                    'codigo'       => $nomenclador->codigo,
                    'descripcion'  => $nomenclador->descripcion,
                    'nom_padre_id' => $nomenclador->nom_padre_id,
                ];
            }
            if ($practica && $practica->nom_padre_id == $this->nom_padre_id) {
                return (object) [
                    'id'           => $practica->id,
                    'codigo'       => $practica->codigo,
                    'descripcion'  => $practica->nombre,
                    'nom_padre_id' => $practica->nom_padre_id,
                ];
            }
        }

        // Sin nom_padre_id (registros antiguos): siempre apuntan a nomenclador
        if ($nomenclador) {
            return (object) [
                'id'           => $nomenclador->id,
                'codigo'       => $nomenclador->codigo,
                'descripcion'  => $nomenclador->descripcion,
                'nom_padre_id' => $nomenclador->nom_padre_id,
            ];
        }

        // Ultimo recurso
        if ($practica) {
            return (object) [
                'id'           => $practica->id,
                'codigo'       => $practica->codigo,
                'descripcion'  => $practica->nombre,
                'nom_padre_id' => $practica->nom_padre_id,
            ];
        }

        return null;
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'profesional_id');
    }

    public function cobertura(): BelongsTo
    {
        return $this->belongsTo(Cobertura::class, 'cobertura_id');
    }
}