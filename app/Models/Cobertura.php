<?php

namespace App\Models;

use App\Models\Valores_cab;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Grupal
 *
 * @property $id
 * @property $nombre
 * @property $cuit
 * @property $porcentaje_adic
 * @property $edad_desde
 * @property $edad_hasta
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 */
class Cobertura extends Model
{
    use SoftDeletes;

    protected $table = 'coberturas';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'cuit', 'porcentaje_adic', 'edad_desde', 'edad_hasta'];
    
    public function valoresCab()
    {
        return $this->hasMany(Valores_cab::class, 'cobertura_id');
    }
}
