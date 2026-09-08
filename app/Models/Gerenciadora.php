<?php

namespace App\Models;

use App\Models\Valores_cab;
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
 * @property $telefono
 * @property $contacto
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 */
class Gerenciadora extends Model
{
    use SoftDeletes;

    protected $table = 'gerenciadoras';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'cuit', 'telefono', 'contacto'];

    public function valores_cab(): HasMany
    {
        return $this->hasMany(Valores_cab::class, 'gerenciadora_id');
    }
    
    public function nomPadres(): BelongsToMany
    {
        return $this->belongsToMany(NomPadre::class, 'gerenciadoras_coberturas_nom_padres', 'gerenciadora_id', 'nom_padre_id');
    }
    
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gerenciadoras_users', 'gerenciadora_id', 'user_id');
    }
 
    public function coberturas(): BelongsToMany
    {
        return $this->belongsToMany(Cobertura::class, 'gerenciadoras_coberturas_nom_padres', 'gerenciadora_id', 'cobertura_id')
                    ->withPivot('nom_padre_id');
    }
}
