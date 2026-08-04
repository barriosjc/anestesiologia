<?php

namespace App\Models;

use App\Models\ValoresCab;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Grupal
 *
 * @property $id
 * @property $nombre
 * @property $cuit
 * @property $telefono
 * @property $contacto
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 */
class Centro extends Model
{
    use SoftDeletes;

    protected $table = 'centros';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'cuit', 'telefono', 'contacto'];

    public function valores(): HasMany
    {
        return $this->hasMany(Valores::class);
    }

    public function valoresCab(): HasMany
    {
        return $this->hasMany(ValoresCab::class, 'centro_id');
    }

    public function presupuestosCab(): HasMany
    {
        return $this->hasMany(PresupuestoCab::class, 'centro_id');
    }
}

