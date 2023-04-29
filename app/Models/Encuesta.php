<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    use HasFactory;
    protected $table = 'encuestas';

    public static function v_encuesta_actual() {
        $resu = Encuesta::query()
        ->join('encuestas_opciones as eo', 'eo.encuestas_id', 'encuestas.id' )
        ->join('opciones as o', 'o.id', 'eo.opciones_id')
        ->join('periodos as p', 'p.encuestas_id', 'encuestas.id')
        ->where('p.desde', '<=', date('Y-m-d'))
        ->where('p.hasta', '>=', date('Y-m-d'))
        ->orderby('eo.orden', 'asc');

        return $resu;
    }
    
    
}
