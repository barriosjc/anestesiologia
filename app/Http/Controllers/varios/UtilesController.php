<?php

namespace App\Http\Controllers;

use App\Models\Tickets;
use App\Models\Descuento;
use App\Models\Parametro;
use Illuminate\Http\Request;
use App\Models\ordenes_compras;
use App\Models\cliente;

class UtilesController extends Controller
{
    //
    public static function traerParamValor($campo)
    {
        $resu = "";
        $data = Parametro::where('campo', $campo)->first();
        if (isset($data)) {
            $resu = $data->valor;
        }

        return $resu;
    }

    public static function traerParamValores($campo)
    {
        $resu = [];
        if(strpos('%', $campo) > 0) {
            $data = Parametro::where('campo', $campo)->select(['id', 'valor'])->get();
        }else {
            $data = Parametro::where('campo', 'like', $campo)->select(['id', 'valor'])->get();
        }
        if (isset($data)) {
            $resu = $data;
        }

        return $resu;
    }

    public static function ProfileCants()
    {
        $user = Auth()->user();
        $email = $user->email;
        $resu['email'] = $email;
        $cerrado = self::traerParamValor('tickt_cerrado');
        $cant_open = ordenes_compras::whereIn('estados_id', [8, 11, 13])
                ->where('users_id', $user->id)->count();
        $resu['cant_open'] = $cant_open;
        $cant_history = ordenes_compras::whereIn('estados_id', [6,7])
                ->where('users_id', Auth()->user()->id)->count();
        $resu['cant_history'] = $cant_history;
        // $cant_tickets = Tickets::where('users_id', $user->id)
        //                     ->where('estado_id','!=', $cerrado)->count();
        // $resu['cant_tickets'] = $cant_tickets;

        return $resu;

    }

    public static function traeDescuentos($campo)
    {
        $resu = [];
        $fechaActual = now()->toDateString();
        $cliente_id = cliente::where('user_id', Auth()->user()->id)->first()->id;
        
        $desc = descuento::query()
            ->where('vigencia_desde', '<=', $fechaActual)
            ->where('vigencia_hasta', '>=', $fechaActual)
            ->where($campo, '!=', null)
            ->where('cupon', null)
            ->where(function ($query) use ($cliente_id) {
                $query->where('clientes_id', '=', $cliente_id)
                    ->orWhere('clientes_id', '=', null);
            })
            ->selectRaw("$campo, MAX(porcentaje) as porcentaje, MAX(prioridad) as prioridad")
            ->groupBy($campo)
            ->orderBy('prioridad', 'desc')
            ->get();

        // Resultado
        $resu = $desc->pluck('porcentaje', $campo)->toArray();

// dd($resu);
        return $resu;
    }

    public static function traeDescuentoProd($id)
    {
        $resu = [];
        $fechaActual = now()->toDateString();
        $cliente_id = cliente::where('user_id', Auth()->user()->id)->first()->id;
        
        $desc = descuento::query()
            ->where('vigencia_desde', '<=', $fechaActual)
            ->where('vigencia_hasta', '>=', $fechaActual)
            ->where('productos_id', $id)
            ->where('cupon', null)
            ->where(function ($query) use ($cliente_id) {
                $query->where('clientes_id', '=', $cliente_id)
                    ->orWhere('clientes_id', '=', null);
            })
            ->selectRaw("productos_id, MAX(porcentaje) as porcentaje, MAX(prioridad) as prioridad")
            ->groupBy('productos_id')
            ->orderBy('prioridad', 'desc')
            ->get();

        // Resultado
        $resu = $desc->pluck('porcentaje', 'productos_id')->toArray();

        if ($resu == []) {
            $desc = descuento::query()
            ->join('categorias as c', 'c.id', '=', 'descuentos.categorias_id')
            ->join('productos as p', function ($join) use ($id) {
                $join->on('p.categorias_id', '=', 'c.id')
                    ->where('p.id', '=', $id);
            })
            ->where('descuentos.vigencia_desde', '<=', $fechaActual)
            ->where('descuentos.vigencia_hasta', '>=', $fechaActual)
            ->where('p.categorias_id', '!=', null)
            ->where('cupon', null)
            ->where(function ($query) use ($cliente_id) {
                $query->where('descuentos.clientes_id', '=', $cliente_id)
                    ->orWhere('descuentos.clientes_id', '=', null);
            })
            ->selectRaw("descuentos.categorias_id as categoria_id, MAX(descuentos.porcentaje) as porcentaje, MAX(descuentos.prioridad) as prioridad")
            ->groupBy('descuentos.categorias_id')
            ->orderBy('prioridad', 'desc')
            ->get();

// dd('cate',$id, $desc[0]['porcentaje']);
            // Resultado
            //$resu = $desc->pluck('porcentaje', $id)->toArray();
            if(isset($desc[0])) {
                $resu[$id] = $desc[0]['porcentaje']; 
            }
        }

        if ($resu == []){
            $desc = descuento::query()
            ->where('vigencia_desde', '<=', $fechaActual)
            ->where('vigencia_hasta', '>=', $fechaActual)
            ->where('clientes_id', '=', $cliente_id)
            ->where('categorias_id', '=', null)
            ->where('productos_id', '=', null)
            ->where('cupon', null)
            ->selectRaw("clientes_id, MAX(porcentaje) as porcentaje, MAX(prioridad) as prioridad")
            ->groupBy('clientes_id')
            ->orderBy('prioridad', 'desc')
            ->get();

            // Resultado
            // $resu = $desc->pluck('porcentaje', 'clientes_id')->toArray();
            if(isset($desc[0])) {
                $resu[$id] = $desc[0]['porcentaje']; 
            }
        }
        return $resu;
    }

    // ingreso de un cupon
    public static function traeDescuentoCodigo($codigo)
    {
        $resu = [];
        $fechaActual = now()->toDateString();
        $cliente_id = cliente::where('user_id', Auth()->user()->id)->first()->id;
        
        $desc = descuento::query()
            ->where('vigencia_desde', '<=', $fechaActual)
            ->where('vigencia_hasta', '>=', $fechaActual)
            ->where('cupon', $codigo)
            ->where(function ($query) use ($cliente_id) {
                $query->where('clientes_id', '=', $cliente_id)
                    ->orWhere('clientes_id', '=', null);
            })
            ->selectRaw("categorias_id, productos_id, MAX(porcentaje) as porcentaje, MAX(prioridad) as prioridad")
            ->groupBy('categorias_id', 'productos_id')
            ->orderBy('prioridad', 'desc')
            ->first();

        // Resultado
        $resu = $desc;
// dd($resu);
        return $resu;
    }
// dd($resu);


}
