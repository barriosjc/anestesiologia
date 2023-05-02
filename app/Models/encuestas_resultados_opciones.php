<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $encuestas_resultados_id
 * @property int $opcion_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class encuestas_resultados_opciones extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['encuestas_resultados_id', 'opcion_id', 'created_at', 'updated_at', 'deleted_at'];
}
