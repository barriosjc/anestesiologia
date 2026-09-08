<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresupuestoPago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'presupuestos_pagos';
    protected $fillable = ['presupuesto_cab_id', 'fecha', 'valor', 'usuario_id'];

    // Relación inversa con PresupuestoCab (Cada pago pertenece a un presupuesto)
    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(PresupuestoCab::class, 'presupuesto_cab_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
