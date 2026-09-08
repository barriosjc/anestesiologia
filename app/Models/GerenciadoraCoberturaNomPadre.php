<?php

namespace App\Models;

use App\Models\NomPadre;
use App\Models\Cobertura;
use App\Models\Gerenciadora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GerenciadoraCoberturaNomPadre extends Model
{
    protected $table = 'gerenciadoras_coberturas_nom_padres';
    
    use SoftDeletes;
    
    protected $fillable = [
        'gerenciadora_id',
        'cobertura_id',
        'nom_padre_id'
    ];
    
    // Relaciones
    public function gerenciadora(): BelongsTo
    {
        return $this->belongsTo(Gerenciadora::class, 'gerenciadora_id');
    }
    
    public function cobertura(): BelongsTo
    {
        return $this->belongsTo(Cobertura::class, 'cobertura_id');
    }
    
    public function nomPadre(): BelongsTo
    {
        return $this->belongsTo(NomPadre::class, 'nom_padre_id');
    }
}
