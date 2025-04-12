<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Valores_cab
 *
 * @property $id
 * @property $gerenciadora_id
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
    protected $fillable = [
        'gerenciadora_id',
        'cobertura_id',
        'centro_id',
        'periodo',
        'grupo'
    ];

    public function cobertura()
    {
        return $this->belongsTo(Cobertura::class, 'cobertura_id');
    }

    public function centro()
    {
        return $this->belongsTo(Centro::class, 'centro_id');
    }

    public function gerenciadora()
    {
        return $this->belongsTo(Gerenciadora::class, 'gerenciadora_id');
    }

    public static function vValores(
        int $gerenciadora_id,
        int $cobertura_id,
        int $centro_id,
        string $periodo,
        string $codigo
        )
    {
        $resu = Valores_cab::query()
            ->select('nv.valor', 'nv.nivel', 'nv.aplica_pocent_adic', 'nv.moneda')
            ->join('nom_valores as nv', 'nv.grupo', 'nom_valores_cab.grupo')
            ->where('nom_valores_cab.gerenciadora_id', $gerenciadora_id)
            ->where('nom_valores_cab.cobertura_id', $cobertura_id)
            ->where('nom_valores_cab.centro_id', $centro_id)
            ->where('nom_valores_cab.periodo', $periodo)
            ->where('nv.nivel', $codigo)
            ->first();
            
            // $sql = $resu->toSql();
            // $bindings = $resu->getBindings();
            // dd($sql, $bindings, $resu->first());

            if ($resu->moneda == 'UDS') {
                $valDolar = Parametro::where('nombre', 'UDS')->first()->valor;
                $resu->valor = round($resu->valor * $valDolar, 2);
                    //  dd($valDolar, $resu);
            }

        return $resu;
    }
}
