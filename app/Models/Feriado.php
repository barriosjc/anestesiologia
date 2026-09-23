<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Feriado
 *
 * @property $id
 * @property $fecha
 * @property $nombre
 * @property $created_at
 * @property $updated_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Feriado extends Model
{
    protected $table = 'feriados';

    protected $fillable = ['fecha', 'nombre'];

    protected $casts = [
        'fecha' => 'date',
    ];
}