<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * Class Empresa
 *
 * @property $id
 * @property $razon_social
 * @property $contacto
 * @property $telefono
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @property Encuesta[] $encuestas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Empresa extends Model
{
    use SoftDeletes;

    static $rules = [
    ];

    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['razon_social','contacto','telefono','uri','logo', 'email_contacto', 'email_nombre'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function encuestas()
    {
        return $this->hasMany('App\Models\Encuesta', 'empresas_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        //id es el id de encuesta
        return $this->hasMany(user::class, 'id');
    }

}
