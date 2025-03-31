<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresupuestoPago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'presupuestos_pagos';
    protected $fillable = ['presupuesto_cab_id', 'fecha', 'valor'];

    // Relación inversa con PresupuestoCab (Cada pago pertenece a un presupuesto)
    public function presupuesto()
    {
        return $this->belongsTo(PresupuestoCab::class, 'presupuesto_cab_id');
    }
}
