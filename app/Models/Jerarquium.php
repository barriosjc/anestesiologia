<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Jerarquium
 *
 * @property $id
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 * @property $users_id
 * @property $users_empresas_id
 * @property $user_jefe_id
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Jerarquium extends Model
{
    use SoftDeletes;

    static $rules = [
		'users_id' => 'required',
		'users_empresas_id' => 'required',
		'user_jefe_id' => 'required',
    ];

    protected $perPage = 20;

    protected $table = 'jerarquias';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['users_id','users_empresas_id','user_jefe_id'];



}
