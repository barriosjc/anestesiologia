<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Periodo
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
class Periodo extends Model
{
    use SoftDeletes;

    static $rules = [
		'descripcion' => 'required',
		'desde' => 'required',
		'hasta' => 'required',
    ];

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion','desde','hasta', 'encuestas_id', 'habilitada'];

    
    public function getDesdeAttribute($value)
    {
        $resu = '';
        if (!empty($value)) {
            $resu = date('d/m/Y', strtotime($value));
        }

        return $resu;
    }

    public function getHastaAttribute($value)
    {
        $resu = '';
        if (!empty($value)) {
            $resu = date('m/d/Y', strtotime($value));
        }

        return $resu;
    }
    
    public function getDesdeyAttribute($value)
    {
        $resu = $this->desde;
        if (!empty($resu)) {
            $resu = substr($resu,6,4)."-".substr($resu,3,2)."-".substr($resu,0,2);
            $resu = date('Y-m-d', strtotime($resu));
        }

        return $resu;
    }    
    
    public function getHastayAttribute($value)
    {
        $resu = $this->hasta;
        if (!empty($resu)) {
            $resu = substr($resu,6,4)."-".substr($resu,3,2)."-".substr($resu,0,2);
            $resu = date('Y-m-d', strtotime($resu));
        }

        return $resu;
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function encuestas()
    {
        return $this->hasMany('App\Models\Encuesta', 'periodos_id', 'id');
    }
    

}
