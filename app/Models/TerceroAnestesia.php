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
        'nomenclador_id',
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

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'profesional_id');
    }

    public function cobertura(): BelongsTo
    {
        return $this->belongsTo(Cobertura::class, 'cobertura_id');
    }
}
