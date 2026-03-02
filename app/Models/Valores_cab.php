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
    ) {
        return app(ValorizacionService::class)->getValor(
            $gerenciadora_id,
            $cobertura_id,
            $centro_id,
            $periodo,
            $codigo
        );
    }
}
