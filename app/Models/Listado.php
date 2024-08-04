<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Grupal
 *
 * @property $id
 * @property $nombre
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 */
class Listado extends Model
{
    use SoftDeletes;

    protected $table = 'listados';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre'];
    
}
