<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresupuestoCab extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'presupuestos_cab';
    protected $fillable = ['fecha', 'nombre', 'fecha_nac', 'dni', 'centro_id', 'observaciones', 'usuario_id'];

    // Relación con PresupuestoDet (Un presupuesto tiene muchos detalles)
    public function detalles()
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
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
