<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $puntos
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $grupal_id
 * @property int $encuestas_resultados_id
 */
class resultado_grupal extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'resultados_grupal';

    /**
     * @var array
     */
    protected $fillable = ['puntos', 'created_at', 'updated_at', 'deleted_at', 'grupal_id', 'encuestas_resultados_opciones_id'];
}
