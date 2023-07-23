<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Empresa;
// use App\Models\Permission;
use Spatie\Permission\Models\Permission;
use Auth;
// use Illuminate\Support\Facades\Auth as FacadesAuth;

class User extends Authenticatable
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable;

    protected $guard = 'clorox';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'foto',
        'empresas_id',
        'cargo',
        'es_jefe',
        'jefe_user_id',
        'telefono',
        'observaciones',
        'area'
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
    public function empresas()
    {
        return $this->belongsTo(empresa::class, 'empresas_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jefes()
    {
        return $this->belongsTo(user::class, 'jefe_user_id');
    }

    public static function validar($permiso, $guard)
    {

        $existe = Permission::where('name', $permiso)
            ->where('guard_name', $guard)
            ->first();

        $resu = false;
        if ($existe) {
            // $resu = Auth()->user()->hasPermissionTo($permiso, $guard);
            // $resu = Auth()->user()->can($permiso);
        }

        return $resu;
    }
}
