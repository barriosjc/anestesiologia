<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * Class parte_cab
 *
 * @property $id
 * @property $gerenciadora_id
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
        , 'gerenciadora_id'
        , 'centro_id'
        , 'paciente_id'
        , 'fec_prestacion'
        , 'fec_prestacion_fin'
        , 'prestador_id'
        , 'profesional_id'
        , 'observacion'
    ];
    
    protected $casts = [
        'fec_prestacion' => 'datetime',
        'fec_prestacion_fin' => 'datetime',
    ];

    // Accessor para el input de fecha de prestación (formato para datetime-local)
    public function getFecPrestacionInputAttribute(): ?string
    {
        return $this->fec_prestacion ? $this->fec_prestacion->format('Y-m-d\TH:i') : null;
    }
    
    // Accessor para el input de fecha de prestación fin (formato para datetime-local)
    public function getFecPrestacionFinInputAttribute(): ?string
    {
        return $this->fec_prestacion_fin ? $this->fec_prestacion_fin->format('Y-m-d\TH:i') : null;
    }
    
    // Accessor para mostrar las fechas en formato dd/mm/yyyy H:i
    public function getFecPrestacionFormattedAttribute(): ?string
    {
        return $this->fec_prestacion ? $this->fec_prestacion->format('d/m/Y H:i') : null;
    }
    
    public function getFecPrestacionFinFormattedAttribute(): ?string
    {
        return $this->fec_prestacion_fin ? $this->fec_prestacion_fin->format('d/m/Y H:i') : null;
    }
    
    // Mutators para guardar las fechas (opcional, Laravel ya maneja datetime-local bien)
    public function setFecPrestacionAttribute($value)
    {
        $this->attributes['fec_prestacion'] = $value ? Carbon::parse($value) : null;
    }
    
    public function setFecPrestacionFinAttribute($value)
    {
        $this->attributes['fec_prestacion_fin'] = $value ? Carbon::parse($value) : null;
    }
    
    public static function vParteCab(): Builder
    {
        $query = DB::table('v_parte_cab')
            ->select('id', 'gerenciadora_id','profesional_id', 'paciente_id', 'cobertura_id', 'centro_id', 'observacion', 'user_id',
                'estado_id', 'created_at', 'deleted_at', 'updated_at', 'fec_prestacion', 'fec_prestacion_orig', 'profesional',
                'centro', 'paciente', 'fec_nacimiento', 'fec_nacimiento_orig', 'name', 'email', 'edad', 'cobertura', 'sigla',
                'est_descripcion', 'est_id', 'cantidad')
            ->orderBy('id', 'desc');
    
        return $query;
    }

}
