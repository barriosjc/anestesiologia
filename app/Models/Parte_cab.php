<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Class parte_cab
 *
 * @property $id
 * @property $centro_id
 * @property $paciente_id
 * @property $fecha_prestacion
 * @property $prestador_id
 * @property $profesional_id
 * @property $observacion
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Parte_cab extends Model
{
    use SoftDeletes;

    protected $perPage = 20;

    protected $table = 'partes_cab';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id'
        , 'centro_id'
        , 'paciente_id'
        , 'fecha_prestacion'
        , 'prestador_id'
        , 'profesional_id'
        , 'observacion'
    ];
        
    public static function vParteCab()
    {
        $query = DB::table('v_parte_cab')
            ->select('id', 'profesional_id', 'paciente_id', 'cobertura_id', 'centro_id', 'observacion', 'user_id',
                'estado_id', 'created_at', 'deleted_at', 'updated_at', 'fec_prestacion', 'fec_prestacion_orig', 'profesional',
                'centro', 'paciente', 'fec_nacimiento', 'fec_nacimiento_orig', 'name', 'email', 'edad', 'cobertura', 'sigla',
                'est_descripcion', 'est_id', 'cantidad')
            ->orderBy('id', 'desc');
    
        return $query;
    }

}
