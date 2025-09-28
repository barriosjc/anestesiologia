<?php

namespace App\Models;

use App\Models\Cobertura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PresupuestoDet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'presupuestos_det';
    protected $fillable = ['presupuesto_cab_id', 'cobertura_id', 'nivel', 'nom_padre_id', 'nomenclador_id', 'porcentaje', 'valor', 'observaciones'];

    // Relación inversa con PresupuestoCab (Cada detalle pertenece a un presupuesto)
    public function presupuestoCab()
    {
        return $this->belongsTo(PresupuestoCab::class, 'presupuesto_cab_id');
    }

    // Relación con Cobertura
    public function cobertura()
    {
        return $this->belongsTo(Cobertura::class, 'cobertura_id');
    }
}
