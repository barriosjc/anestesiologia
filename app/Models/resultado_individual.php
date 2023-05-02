<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property integer $users_id
 * @property int $puntos
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $encuestas_resultados_id
 * @property User $user
 */
class resultado_individual extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'resultados_individual';

    /**
     * @var array
     */
    protected $fillable = ['puntos', 'created_at', 'updated_at', 'deleted_at', 'encuestas_resultados_opciones_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'users_id');
    }
}
