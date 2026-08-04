<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ParteDet
 *
 * @property $id
 * @property $parte_cab_id
 * @property $documento_id
 * @property $nro_hoja
 * @property $path
 * @property $created_at
 * @property $updated_at
 * @property $deleted_at
 
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class ParteDet extends Model
{
    use SoftDeletes;

    protected $perPage = 20;

    protected $table = 'partes_det';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id'
        , 'parte_cab_id'
        , 'documento_id'
        , 'path'
        , 'nro_hoja'
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }
}
