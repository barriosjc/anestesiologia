<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Opcione
 *
 * @property $id
 * @property $descripcion
 * @property $detalle
 * @property $imagen
 * @property $style
 * @property $habilitada
 * @property $puntos
 * @property $empresas_id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property EncuestasOpcione[] $encuestasOpciones
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Opcion extends Model
{
    use SoftDeletes;

    static $rules = [
		'descripcion' => 'required',
		'detalle' => 'required',
		'imagen' => 'required',
		'style' => 'required',
    'habilitada' => 'required',
    'puntos' => 'required',
    ];

    protected $table = 'opciones';
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['empresas_id','descripcion','detalle','imagen','style','habilitada','puntos','puntos_min','puntos_max'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function encuestasOpciones()
    {
        return $this->hasMany('App\Models\EncuestasOpcion', 'opciones_id', 'id');
    }
    

}
