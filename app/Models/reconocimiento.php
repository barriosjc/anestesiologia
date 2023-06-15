<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class reconocimiento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reconocimientos';
    protected $perPage = 5;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['users_id', 'motivo'];

    public static function v_reconocimientos() {
        $resu = reconocimiento::query()
        ->select(['u.*' , 'reconocimientos.id as reconocimiento_id', 'motivo', 'reconocimientos.created_at', DB::raw('concat(u.last_name, " - ", u.email) as descripcion ')])
        ->join('users as u', 'u.id', 'users_id')
        ->orderby('reconocimientos.created_at', 'desc');

        return $resu;
    }


}
