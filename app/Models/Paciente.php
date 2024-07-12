<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Paciente
 *
 * @property $id
 * @property $nombre
 * @property $dni
 * @property $telefono
 * @property $fec_nacimiento
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 *
 */
class paciente extends Model
{
    use SoftDeletes;

    protected $table = 'pacientes';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'cuil', 'telefono', 'fec_nacimiento'];
    
    
    //formato para mostrar en los inputs
    public function getFecNacimientoAttribute($value)
    {
        $resu = '';
        if (!empty($value)) {
            $resu = date('Y-m-d', strtotime($value));
        }

        return $resu;
    }
}
