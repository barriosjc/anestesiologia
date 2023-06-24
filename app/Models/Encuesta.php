<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class Encuesta extends Model
{
    use SoftDeletes;
    protected $fillable = ['encuesta', 'edicion', 'habilitada', 'empresas_id', 'periodos_id', 'opcionesxcol'];

    protected $table = 'encuestas';

    public static function v_encuesta_actual($empresas_id) {
        $resu = Encuesta::query()
        ->select(['eo.encuestas_id', 'encuesta', 'edicion', 'encuestas.habilitada as e_habilitada', 'encuestas.empresas_id', 
        'opcionesxcol', 'p.descrip_rango', 'p.desde', 'p.hasta', 'e.razon_social',
        'eo.id as encuestas_opciones_id', 'opciones_id', 'o.puntos as o_puntos', 'eo.orden',
        'descripcion', 'detalle', 'imagen', 'o.style', 'o.habilitada as o_habilitada', 'o.puntos as o_puntos'])
        ->join('encuestas_opciones as eo', 'eo.encuestas_id', 'encuestas.id' )
        ->join('opciones as o', 'o.id', 'eo.opciones_id')
        ->join('periodos as p', 'p.encuestas_id', 'encuestas.id')
        ->join('empresas as e', 'e.id', 'encuestas.empresas_id')
        ->where('p.desde', '<=', date('Y-m-d'))
        ->where('p.hasta', '>=', date('Y-m-d'))
        ->where('p.habilitada', '=', 1)
        ->where('eo.habilitada', '=', 1)
        ->where('encuestas.habilitada', '=', 1)
        ->where('e.id', '=' , $empresas_id)
        ->whereNull('p.deleted_at')
        ->whereNull('o.deleted_at')
        ->whereNull('eo.deleted_at')
        ->orderby('eo.orden', 'asc');
// dd("tabla encuesta",$resu);
        return $resu;
    }
    
    public static function v_encuestas($empresa_id) {
        $resu = Encuesta::query()
        ->select(['encuestas.id', 'encuesta', 'edicion', 'encuestas.habilitada', 
                    'opcionesxcol', 'empresas_id', 'razon_social'])
        ->join('empresas as e', 'e.id', 'encuestas.empresas_id')
        ->where('e.id', $empresa_id)
        ->orderby('encuestas.created_at', 'desc');
// dd("tabla encuesta",$resu);
        return $resu;
    }
    
}
