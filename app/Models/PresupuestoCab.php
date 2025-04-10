<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresupuestoCab extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'presupuestos_cab';
    protected $fillable = ['fecha', 'nombre', 'fecha_nac', 'dni', 'centro_id', 'profesional_id', 'observaciones', 'usuario_id', 'estado'];

    public function getPacienteAttribute()
    {
        $datos = [$this->nombre];
    
        if ($this->dni) {
            $datos[] = $this->dni;
        }
    
        if ($this->fecha_nac) {
            $edad = now()->diffInYears($this->fecha_nac);
            $datos[] = $edad;
        }
    
        return implode(' - ', $datos);
    }

    public function presupuestosDet()
    {
        return $this->hasMany(PresupuestoDet::class, 'presupuesto_cab_id');
    }

    // Relación con PresupuestoPago (Un presupuesto tiene muchos pagos)
    public function pagos()
    {
        return $this->hasMany(PresupuestoPago::class, 'presupuesto_cab_id');
    }

    // Relación con Centro
    public function centro()
    {
        return $this->belongsTo(Centro::class, 'centro_id');
    }

    // Relación con Usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
