<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Grupal
 *
 * @property $id
 * @property $gerenciador_id
 * @property $cobertura_id
 * @property $centro_id
 * @property $periodo_id
 * @property $grupo
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 */
class Valores_cab extends Model
{
    use SoftDeletes;

    protected $table = 'nom_valores_cab';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['grupo', 'nivel', 'valor', 'tipo'];
}
