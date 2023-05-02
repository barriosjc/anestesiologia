<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $encuestas_opciones_id
 * @property string $observaciones
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $users_id
 * @property string $adjunto
 * @property EncuestasOpcione $encuestasOpcione
 */
class encuesta_resultado extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'encuestas_resultados';

    /**
     * @var array
     */
    protected $fillable = ['encuestas_opciones_id', 'observaciones', 'created_at', 'updated_at', 'deleted_at', 'users_id', 'adjunto'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function encuestasOpcion()
    {
        return $this->belongsTo('App\EncuestasOpcion', 'encuestas_opciones_id');
    }
}
