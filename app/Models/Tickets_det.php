<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $tickets_id
 * @property string $insidente
 * @property int $users_id
 * @property int $users_id_rta
 * @property string $original_path
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $obs
 * @property int $estado_id
 * @property Ticket $ticket
 */
class Tickets_det extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'tickets_det';

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['tickets_id', 'insidente', 'users_id', 'cut_paht', 'print_path', 'cli_imp', 'created_at', 'updated_at', 'deleted_at'];


    public static function v_listar($ordenes_det_id) {

        $resu = Tickets_det::query()
            ->join('users', 'users.id', 'tickets_det.users_id')
            ->join('ordenes_compras_det as det', 'det.id', 'ordenes_compras_det_id')
            ->join('productos as p', 'p.id', 'det.productos_id')
            ->join('categorias as c', 'c.id', 'p.categorias_id')
            ->join('ordenes_compras as oc', 'oc.id', 'ordenes_compras_id')
            ->join('clientes as cli', 'cli.user_id', 'oc.users_id')
            ->select(['tickets_det.*', 'users.name', 'users.email', 'cli.id as cli_id',
                    'cli.first_name', 'cli.last_name', 'oc.users_id as cli_users_id',
                    'c.descripcion as cat_desc', 'p.nombre', 'width_feet', 'width_inches', 'height_feet', 'height_inches'])
            ->where('ordenes_compras_det_id', $ordenes_det_id)
            ->orderby('tickets_det.created_at', 'asc');
            
        return $resu;
    }
}
