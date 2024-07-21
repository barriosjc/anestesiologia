<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class nomenclador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nomenclador';
    
    protected $perPage = 20;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['cobertura_id', 'centro_id', 'valor', 'nivel', 'tipo'];

}
