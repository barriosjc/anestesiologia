<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $opciones_id
 * @property int $encuestas_id
 * @property int $puntos
 * @property int $puntos_min
 * @property int $puntos_max
 * @property int $orden
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property Encuesta $encuesta
 * @property Opcion $opcion
 * @property EncuestasResultado[] $encuestasResultados
 */
class encuesta_opcion extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'encuestas_opciones';

    /**
     * @var array
     */
    protected $fillable = ['opciones_id', 'encuestas_id', 'puntos', 'puntos_min', 'puntos_max', 'orden', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function encuesta()
    {
        return $this->belongsTo('App\Encuesta', 'encuestas_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function opcion()
    {
        return $this->belongsTo('App\Opcion', 'opciones_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function encuestasResultados()
    {
        return $this->hasMany('App\EncuestasResultado', 'encuestas_opciones_id');
    }
}
