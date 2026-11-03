<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nivel extends Model
{
    protected $table = 'niveles';

    /**
     * La tabla `niveles` es preexistente y no tiene created_at / updated_at.
     */
    public $timestamps = false;

    protected $fillable = [
        'nivel',
        'cod_uom',
        'descripcion',
    ];

    public function detallesAuditoria(): HasMany
    {
        return $this->hasMany(OrdenPagoAuditDet::class, 'nivel_id');
    }
}
