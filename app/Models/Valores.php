<?php

namespace App\Models;

use App\Models\Centro;
use App\Models\Cobertura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Grupal
 *
 * @property $id
 * @property $cobertura_id
 * @property $centro_id
 * @property $nivel
 * @property $valor
 * @property $tipo
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 */
class Valores extends Model
{
    use SoftDeletes;

    protected $table = 'nom_valores';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['cobertura_id', 'centro_id', 'nivel', 'valor', 'tipo'];

    public function centro()
    {
        return $this->belongsTo(Centro::class);
    }

    public function cobertura()
    {
        return $this->belongsTo(Cobertura::class);
    }
}
