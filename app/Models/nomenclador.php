<?php

namespace App\Models;

use App\Models\NomPadre;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nomenclador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nomenclador';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nom_padre_id', 'organo_id', 'cobertura_id',  'codigo', 'nivel', 'descripcion', 'tipo'];

    public function nomPadre(): BelongsTo
    {
        return $this->belongsTo(NomPadre::class, 'nom_padre_id');
    }

    

}
