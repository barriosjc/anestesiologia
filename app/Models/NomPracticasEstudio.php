<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NomPracticasEstudio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nom_practicas_estudios';

    protected $fillable = [
        'nom_padre_id',
        'nombre',
        'codigo',
    ];

    public function nomPadre()
    {
        return $this->belongsTo(NomPadre::class, 'nom_padre_id');
    }
}