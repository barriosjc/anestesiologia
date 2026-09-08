<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calendar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'calendarios';
    
    protected $fillable = ['user_id', 'fecha_ini', 'fecha_fin', 'observaciones', 'cerrado'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id');
    }

}
