<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class GuardiaMedico
 *
 * @property $id
 * @property $fecha
 * @property $medico_id
 * @property $es_sabado
 * @property $es_domingo
 * @property $feriado
 * @property $color
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class GuardiaMedico extends Model
{
    protected $table = 'guardias_medicos';

    protected $fillable = ['fecha', 'medico_id', 'es_sabado', 'es_domingo', 'feriado', 'color'];

    protected $casts = [
        'fecha' => 'date',
        'es_sabado' => 'boolean',
        'es_domingo' => 'boolean',
    ];

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'medico_id');
    }
}