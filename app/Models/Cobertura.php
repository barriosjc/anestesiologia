<?php

namespace App\Models;

use App\Models\NomPadre;
use App\Models\Valores_cab;
use App\Models\Gerenciadora;
use App\Models\PresupuestoDet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    
    public function valoresCab(): HasMany
    {
        return $this->hasMany(Valores_cab::class, 'cobertura_id');
    }

    public function presupuestosDet(): HasMany
    {
        return $this->hasMany(PresupuestoDet::class, 'cobertura_id');
    }

    public function nomPadres(): BelongsToMany
    {
        return $this->belongsToMany(NomPadre::class, 'cobertura_nom_padre', 'cobertura_id', 'nom_padre_id');
    }
    
    public function gerenciadoras(): BelongsToMany
    {
        return $this->belongsToMany(Gerenciadora::class, 'gerenciadoras_coberturas_nom_padres', 'cobertura_id', 'gerenciadora_id')
                    ->withPivot('nom_padre_id');
    }
}
