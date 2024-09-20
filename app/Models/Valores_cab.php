<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Grupal
 *
 * @property $id
 * @property $gerenciador_id
 * @property $cobertura_id
 * @property $centro_id
 * @property $periodo_id
 * @property $grupo
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 */
class Valores_cab extends Model
{
    use SoftDeletes;

    protected $table = 'nom_valores_cab';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['gerenciador_id',
                            'cobertura_id',
                            'centro_id',
                            'periodo',
                            'grupo'];

    public static function v_valores (int $gerenciador_id, int $cobertura_id, int $centro_id, string $periodo, string $codigo) {

        $resu = Valores_cab::query()
        ->select('valor', 'nivel', )
        ->join('nom_valores as nv','nv.grupo', 'nvc.grupo')
        ->join('nomenclador as n', 'n.nivel', 'nv.nivel');
    
        return $resu;
    }

    select * 
    from nom_valores_cab as nvc		
    inner join nom_valores as nv on nv.grupo = nvc.grupo
    inner join nomenclador as n on n.nivel = nv.nivel
where gerenciador_id = 1 and cobertura_id = 1 and centro_id = 1
and periodo = "2024/09" and codigo = "02-01-04"
}
