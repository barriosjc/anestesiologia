<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Grupal
 *
 * @property $id
 * @property $grupo
 * @property $nivel
 * @property $valor
 * @property $tipo
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 */
class Valores extends Model
{
    use SoftDeletes;

    protected $table = 'nom_valores';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['grupo', 'nivel', 'valor', 'tipo'];

}
