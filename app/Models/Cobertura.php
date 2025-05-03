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

    public function presupuestosDet()
    {
        return $this->hasMany(PresupuestoDet::class, 'cobertura_id');
    }

    public function nomPadres()
    {
        return $this->belongsToMany(NomPadre::class, 'cobertura_nom_padre', 'cobertura_id', 'nom_padre_id');
    }
    
    public function gerenciadoras()
    {
        return $this->belongsToMany(Gerenciadora::class, 'gerenciadoras_coberturas_nom_padres', 'cobertura_id', 'gerenciadora_id')
                    ->withPivot('nom_padre_id');
    }
}
