<?php

namespace App\Models;

use App\Models\Nomenclador;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NomPadre extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nombre', 'tipo'];

    public function coberturas(): BelongsToMany
    {
        return $this->belongsToMany(Cobertura::class, 'cobertura_nom_padre', 'nom_padre_id', 'cobertura_id');
    }
    
    public function gerenciadoras(): BelongsToMany
    {
        return $this->belongsToMany(Gerenciadora::class, 'gerenciadoras_nom_padres', 'nom_padre_id', 'gerenciadora_id');
    }
    
    // public function gerenciadorasCoberturasNomPadres()
    // {
    //     return $this->belongsToMany(GerenciadoraCoberturaNomPadre::class, 'gerenciadoras_coberturas_nom_padres', 'nom_padre_id', 'gerenciadora_id');
    // }
    
    public function nomencladores(): HasMany
    {
        return $this->hasMany(Nomenclador::class, 'nom_padre_id');
    }
}
