<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    use SoftDeletes;
    protected $fillable = ['encuesta', 'edicion', 'habilitada', 'empresas_id', 'periodos_id', 'opcionesxcol'];

    protected $table = 'encuestas';

    public static function v_encuesta_actual() {
        $resu = Encuesta::query()
        ->select(['eo.encuestas_id', 'encuesta', 'edicion', 'encuestas.habilitada as e_habilitada', 'empresas_id', 
        'periodos_id', 'opcionesxcol', 'p.descrip_rango', 'p.desde', 'p.hasta',
        'eo.id as encuestas_opciones_id', 'opciones_id', 'eo.puntos as eo_puntos', 'orden',
        'descripcion', 'detalle', 'imagen', 'style', 'o.habilitada as o_habilitada', 'o.puntos as o_puntos'])
        ->join('encuestas_opciones as eo', 'eo.encuestas_id', 'encuestas.id' )
        ->join('opciones as o', 'o.id', 'eo.opciones_id')
        ->join('periodos as p', 'p.encuesta_id', 'encuestas.id')
        ->where('p.desde', '<=', date('Y-m-d'))
        ->where('p.hasta', '>=', date('Y-m-d'))
        ->orderby('eo.orden', 'asc');
// dd("tabla encuesta",$resu);
        return $resu;
    }
    
    public static function v_encuestas() {
        $resu = Encuesta::query()
        ->select(['encuestas.id', 'encuesta', 'edicion', 'encuestas.habilitada', 'empresas_id', 'razon_social'])
        ->join('empresas as e', 'e.id', 'encuestas.empresas_id')
        ->orderby('encuestas.created_at', 'desc');
// dd("tabla encuesta",$resu);
        return $resu;
    }
    
}
