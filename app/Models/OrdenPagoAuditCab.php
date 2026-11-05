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
        'retencion_caja',
        'retencion_gastos',
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
            'retencion_caja'       => 'decimal:2',
            'retencion_gastos'     => 'decimal:2',
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

    /**
     * Últimos 8 dígitos del nro_op sin ceros a la izquierda.
     * Ej: OPL X 0000-00077740 -> 77740
     */
    public function getNroOp8Attribute(): string
    {
        $nroOp = (string) $this->nro_op;

        // Cuando dos documentos traen la misma OP, el segundo se guarda como
        // "<numero>-<docId>" para no chocar con el indice unico. Ese sufijo no
        // es parte del numero y no debe aparecer en la columna.
        if ($this->documento_llamaparse_id !== null) {
            $nroOp = preg_replace('/-'.$this->documento_llamaparse_id.'$/', '', $nroOp) ?? $nroOp;
        }

        $digitos = preg_replace('/\D/', '', $nroOp);

        if ($digitos === '') {
            return '-';
        }

        $ultimos8 = substr($digitos, -8);
        return (string) (int) $ultimos8; // quita ceros a la izquierda
    }

    /**
     * Retención = fila "Caja de Prof. de la salud" de la hoja Valores.
     *
     * NULL cuando el archivo no informa el dato (no es lo mismo que cero).
     */
    public function getCajaProfSaludAttribute(): ?float
    {
        return $this->retencion_caja === null
            ? null
            : round((float) $this->retencion_caja, 2);
    }

    /**
     * Gastos administrativos = filas "Gastos Administrativos 11%" y "5%".
     *
     * NULL cuando el archivo no informa el dato (no es lo mismo que cero).
     */
    public function getGastosAdminAttribute(): ?float
    {
        return $this->retencion_gastos === null
            ? null
            : round((float) $this->retencion_gastos, 2);
    }

    /**
     * Retención de Ingresos Brutos (IB). Muchas OP no la tienen (queda en 0).
     */
    public function getIbAttribute(): float
    {
        return round((float) $this->op_retenciones, 2);
    }

    /**
     * A pagar = Total - IB - Caja Prof. Salud - Gastos Admin.
     *
     * NULL cuando el archivo no informa retenciones: sin esos datos no se
     * puede calcular el neto de la orden de pago.
     */
    public function getAPagarAttribute(): ?float
    {
        $caja   = $this->caja_prof_salud;
        $gastos = $this->gastos_admin;

        if ($caja === null && $gastos === null) {
            return null;
        }

        return round(
            (float) $this->op_total
            - (float) $this->ib
            - (float) ($caja ?? 0)
            - (float) ($gastos ?? 0),
            2
        );
    }
}
