<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
<<<<<<< HEAD
use App\Models\Parte_det;
=======
use App\Models\ParteDet;
>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public static array $rules = [
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

    public function parteDets(): HasMany
    {
        return $this->hasMany(ParteDet::class);
    }
    
    public function profesionales(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'profesionales_docum', 'documento_id', 'profesional_id');
    }

}
