<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NomPadre extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nombre', 'tipo'];

    public function coberturas()
    {
        return $this->belongsToMany(Cobertura::class, 'cobertura_nom_padre', 'nom_padre_id', 'cobertura_id');
    }

}
