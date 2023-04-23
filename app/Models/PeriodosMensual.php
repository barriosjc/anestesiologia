<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PeriodosMensual
 *
 * @property $id
 * @property $descripcion
 * @property $desde
 * @property $hasta
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Encuesta[] $encuestas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class PeriodosMensual extends Model
{
    use SoftDeletes;

    static $rules = [
		'descripcion' => 'required',
		'desde' => 'required',
		'hasta' => 'required',
    ];

    protected $perPage = 20;

    protected $table = 'periodos_mensual';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion','desde','hasta'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function encuestas()
    {
        return $this->hasMany('App\Models\Encuesta', 'periodos_mensual_id', 'id');
    }
    

}
