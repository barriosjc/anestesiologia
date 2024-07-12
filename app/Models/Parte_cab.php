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
        
    public static function v_parte_cab() {

        $query = DB::table('v_parte_cab')
            ->orderBy('id', 'desc');
    
        return $query;
    }

}
