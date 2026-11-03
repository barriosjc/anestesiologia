<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenPagoAuditDet extends Model
{
    protected $table = 'ordenes_pagos_audit_det';

    protected $fillable = [
        'orden_pago_audit_cab_id',
        'parte_cab_id',
        'consumo_det_id',
        'cobertura_id',
        'nivel_id',
        'atencion',
        'fecha_archivo',
        'fecha_sistema',
        'obra_social_archivo',
        'cod_uom_archivo',
        'cod_uom_sistema',
        'periodo_archivo',
        'codigo_archivo',
        'nivel_sistema',
        'codigo_sistema',
        'descripcion_archivo',
        'descripcion_sistema',
        'valor_archivo',
        'valor_sistema',
        'diferencia',
        'porcentaje_diferencia',
        'porcentaje_archivo',
        'porcentaje_sistema',
        'severidad',
        'estado',
        'dif_fecha',
        'dif_obra_social',
        'dif_descripcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_archivo'        => 'date',
            'fecha_sistema'        => 'date',
            'valor_archivo'        => 'decimal:2',
            'valor_sistema'        => 'decimal:2',
            'diferencia'           => 'decimal:2',
            'porcentaje_diferencia' => 'decimal:2',
            'porcentaje_archivo'   => 'decimal:2',
            'porcentaje_sistema'   => 'decimal:2',
            'dif_fecha'            => 'boolean',
            'dif_obra_social'      => 'boolean',
            'dif_descripcion'      => 'boolean',
        ];
    }

    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenPagoAuditCab::class, 'orden_pago_audit_cab_id');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'nivel_id');
    }

    public function parte(): BelongsTo
    {
        return $this->belongsTo(ParteCab::class, 'parte_cab_id');
    }

    public function cobertura(): BelongsTo
    {
        return $this->belongsTo(Cobertura::class, 'cobertura_id');
    }

    public function consumoDet(): BelongsTo
    {
        return $this->belongsTo(ConsumoDet::class, 'consumo_det_id');
    }

    public function getSeveridadLabelAttribute(): string
    {
        return match ($this->severidad) {
            'alta'  => 'Alta',
            'media' => 'Media',
            'baja'  => 'Baja',
            default => 'Sin diferencia',
        };
    }

    public function getSeveridadBadgeAttribute(): string
    {
        return match ($this->severidad) {
            'alta'  => 'bg-danger text-white',
            'media' => 'bg-warning text-dark',
            'baja'  => 'bg-info text-dark',
            default => 'bg-success text-white',
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'diferencia'          => 'Diferencia',
            'sin_parte'           => 'No está en sistema (atención)',
            'sin_nivel'           => 'No está en sistema (código)',
            'sin_consumo'         => 'No está en sistema (práctica)',
            'sin_archivo'         => 'Falta en el archivo',
            'sin_correspondencia' => 'Sin contraparte en sistema',
            default               => 'Coincide',
        };
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'ok'                  => 'bg-success text-white',
            'diferencia'          => 'bg-warning text-dark',
            'sin_correspondencia' => 'bg-secondary text-white',
            default               => 'bg-danger text-white',
        };
    }

    /**
     * Indica si el importe del archivo difiere del sistema. Se usa para
     * marcar el valor con un punto rojo en la tabla de auditoria.
     */
    public function getDifValorAttribute(): bool
    {
        return abs((float) $this->diferencia) >= 0.01;
    }

    public function getEsFaltanteAttribute(): bool
    {
        return in_array($this->estado, ['sin_parte', 'sin_nivel', 'sin_consumo', 'sin_archivo', 'sin_correspondencia'], true);
    }
}
