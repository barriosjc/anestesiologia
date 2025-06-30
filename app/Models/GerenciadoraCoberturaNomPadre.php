<?php

namespace App\Models;

use App\Models\NomPadre;
use App\Models\Cobertura;
use App\Models\Gerenciadora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    public function gerenciadora()
    {
        return $this->belongsTo(Gerenciadora::class, 'gerenciadora_id');
    }
    
    public function cobertura()
    {
        return $this->belongsTo(Cobertura::class, 'cobertura_id');
    }
    
    public function nomPadre()
    {
        return $this->belongsTo(NomPadre::class, 'nom_padre_id');
    }
}
