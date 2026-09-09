<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfesionalDocumento extends Model
{
    use HasFactory, SoftDeletes; 

    protected $table = 'profesionales_docum'; 
    
    protected $fillable = [
        'path',
        'profesional_id',
        'documento_id',
        'nro_hoja',
        'fecha_vcto'
    ];

    public function getFechaVctoAtAttribute($value): string
    {
        $resu = '';
        if (!empty($value)) {
            $resu = date('d/m/Y', strtotime($value));
        }

        return $resu;
    }

    public function getFechaVctoyAttribute(): ?string
    {
        $resu = $this->fecha_vcto;
        if (!empty($resu)) {
            $resu = substr($resu,0,4)."-".substr($resu,5,2)."-".substr($resu,8,2);
            // dd($resu);
            $resu = date('d/m/Y', strtotime($resu));
        }

        return $resu;
    }

    public $timestamps = true;

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }
}
