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

    public function valores_cab()
    {
        return $this->hasMany(Valores_cab::class, 'gerenciadora_id');
    }
    
}
