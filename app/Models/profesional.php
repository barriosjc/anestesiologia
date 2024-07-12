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
 * @property $id
 * @property $nombre
 * @property $matricula
 * @property $email
 * @property $telefono
 * @property $estado
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Profesional
 extends Model
{
    use SoftDeletes;

    protected $perPage = 20;

    protected $table = 'profesionales';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['id','nombre','email','matricula','telefono','estado'];



}
