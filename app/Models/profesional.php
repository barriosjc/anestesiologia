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
 * @property $dni
 * @property $email
 * @property $telefono
 * @property $estado
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Profesional extends Model
{
    use SoftDeletes;

    protected $perPage = 20;

    protected $table = 'profesionales';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['id','nombre','email','dni','telefono'];

    public function documentos()
    {
        return $this->belongsToMany(Documento::class, 'profesionales_docum', 'profesional_id', 'documento_id');
    }

}
