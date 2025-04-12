<?php

namespace App\Models;

use Auth;
use App\Models\Centro;
use App\Models\Calendar;
use App\Models\Gerenciadora;
use App\Models\PresupuestoCab;
use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

// use Illuminate\Support\Facades\Auth as FacadesAuth;

class User extends Authenticatable
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'foto',
        'centro_id',
        'telefono'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function centro()
    {
        return $this->belongsTo(Centro::class, 'centro_id');
    }

    public function calendar()
    {
        return $this->hasMany(Calendar::class, 'user_id');
    }

    public function presupuestosCab()
    {
        return $this->hasMany(PresupuestoCab::class, 'usuario_id');
    }

    public function gerenciadoras()
    {
        return $this->belongsToMany(Gerenciadora::class, 'gerenciadoras_users', 'user_id', 'gerenciadora_id');
    }
}
