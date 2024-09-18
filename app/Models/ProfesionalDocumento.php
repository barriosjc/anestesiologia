<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

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

    public $timestamps = true;

    public function profesional()
    {
        return $this->belongsTo(Profesional::class);
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }
}
