<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Grupal
 *
 * @property $id
 * @property $descripcion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property GrupalUser[] $grupalUsers
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Grupal extends Model
{
    use SoftDeletes;

    static $rules = [
		'descripcion' => 'required',
    ];

    protected $table = 'grupal';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['descripcion'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function grupalUsers()
    {
        return $this->hasMany('App\Models\GrupalUser', 'grupal_id', 'id');
    }
    

}
