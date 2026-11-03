<?php

namespace App\Models;

use App\Enums\EstadoDocumentoLlamaparse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoLlamaparse extends Model
{
    protected $table = 'documentos_llamaparse';

    protected $fillable = [
        'uuid',
        'user_id',
        'archivo',
        'nombre_original',
        'mime',
        'tamano',
        'estado',
        'job_id',
        'tier',
        'progreso',
        'progreso_mensaje',
        'respuesta',
        'markdown',
        'items',
        'xlsx_archivo',
        'json',
        'json_crudo',
        'usage_credits',
        'error',
        'procesado_at',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoDocumentoLlamaparse::class,
            'respuesta' => 'array',
            'markdown' => 'array',
            'items' => 'array',
            'json' => 'array',
            'json_crudo' => 'array',
            'usage_credits' => 'decimal:2',
            'tamano' => 'integer',
            'progreso' => 'integer',
            'procesado_at' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
