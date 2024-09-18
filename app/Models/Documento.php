<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Parte_det;

/**
 * Class Documento
 *
 * @property $id
 * @property $nombre
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Documento extends Model
{
    use SoftDeletes;

    static $rules = [
		'nombre' => 'required',
    ];

    protected $table = 'documentos';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre'];

    public function parteDets()
    {
        return $this->hasMany(Parte_det::class);
    }
    
    public function profesionales()
    {
        return $this->belongsToMany(Profesional::class, 'profesionales_docum', 'documento_id', 'profesional_id');
    }

}
