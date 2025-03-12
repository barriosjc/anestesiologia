<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NomPadre extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['descripcion', 'tipo'];

}
