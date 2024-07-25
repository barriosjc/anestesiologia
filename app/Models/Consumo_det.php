<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consumo_det extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'consumos_det';
}
