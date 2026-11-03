<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cabecera de ordenes de pago auditadas.
 *
 * Guarda los datos de la OP (nro_op, profesional, fecha, totales) y, cuando
 * la auditoria se genero desde un JSON de LlamaParse, el documento de origen
 * y los totales comparados contra la base de datos.
 */
class OrdenPagoAuditCab extends Model
{
    protected $table = 'ordenes_pagos_audit_cab';

    protected $fillable = [
        'documento_llamaparse_id',
        'nro_op',
        'nro_liquidacion',
        'profesional_id',
        'fecha',
        'op_total',
        'total_sistema',
        'total_diferencia',
        'cantidad_diferencias',
        'auditado_at',
        'op_retenciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha'                => 'date',
            'op_total'             => 'decimal:2',
            'op_retenciones'       => 'decimal:2',
            'total_sistema'        => 'decimal:2',
            'total_diferencia'     => 'decimal:2',
            'cantidad_diferencias' => 'integer',
            'auditado_at'          => 'datetime',
        ];
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class, 'profesional_id');
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(DocumentoLlamaparse::class, 'documento_llamaparse_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenPagoAuditDet::class, 'orden_pago_audit_cab_id');
    }
}
