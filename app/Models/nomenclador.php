<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nomenclador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nomenclador';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['organo_id', 'cobertura_id',  'codigo', 'descripcion', 'tipo'];

}
