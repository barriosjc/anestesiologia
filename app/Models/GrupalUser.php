<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class GrupalUser
 *
 * @property $id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 * @property $grupal_id
 * @property $users_id
 *
 * @property Grupal $grupal
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class GrupalUser extends Model
{
    use SoftDeletes;

    static $rules = [
		'grupal_id' => 'required',
		'users_id' => 'required',
    ];

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['grupal_id','users_id'];

    protected $table = 'grupal_users';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function grupal()
    {
        return $this->hasOne('App\Models\Grupal', 'id', 'grupal_id');
    }
    

}
