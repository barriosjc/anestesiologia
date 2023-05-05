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
    protected $fillable = ['opciones_id', 'encuestas_id', 'puntos', 'puntos_min', 'puntos_max', 'orden', 'style','created_at', 'updated_at', 'deleted_at'];

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

    public static function v_opciones_encuestas() {
        $resu = encuesta_opcion::query()
        ->select(['eo.encuestas_id', 'encuesta', 'edicion', 'encuestas.habilitada as e_habilitada', 'empresas_id', 
        'periodos_id', 'opcionesxcol', 'p.descrip_rango', 'p.desde', 'p.hasta',
        'eo.id as encuestas_opciones_id', 'opciones_id', 'eo.puntos as eo_puntos', 'orden',
        'descripcion', 'detalle', 'imagen', 'style', 'o.habilitada as o_habilitada', 'o.puntos as o_puntos'])
        ->join('encuestas_opciones as eo', 'eo.encuestas_id', 'encuestas.id' )
        ->join('opciones as o', 'o.id', 'eo.opciones_id')
        ->join('periodos as p', 'p.id', 'periodos_id')
        ->where('p.desde', '<=', date('Y-m-d'))
        ->where('p.hasta', '>=', date('Y-m-d'))
        ->orderby('eo.orden', 'asc');
// dd("tabla encuesta",$resu);
        return $resu;
    }
}
