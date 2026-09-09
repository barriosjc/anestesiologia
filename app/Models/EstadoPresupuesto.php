<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoPresupuesto extends Model
{
    use HasFactory;

    protected $table = 'estados_presupuestos';

    protected $fillable = ["descripcion"];

}
