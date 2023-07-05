<?php

namespace App\Http\Controllers\varios;

use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function show() {

        $participantes = DB::select("SELECT COUNT(*) AS cantidad, 
                                    MAX(DATE_FORMAT(er.created_at, '%m')) AS n_mes, 
                                    DATE_FORMAT(er.created_at, '%b') AS mes
                                    FROM encuestas_resultados as er
                                    INNER JOIN encuestas as en ON en.id = er.encuestas_id 
                                    INNER JOIN periodos as p ON p.encuestas_id = en.id
                                    INNER JOIN empresas as e ON e.id = en.empresas_id
                                                    and e.id = " . session('empresa')->id . 
                                    "  WHERE p.desde <= DATE_FORMAT(CURDATE(), '%Y-%m-%d')
                                        AND p.hasta >= DATE_FORMAT(CURDATE(), '%Y-%m-%d')
                                        AND p.habilitada = 1
                                        AND en.habilitada = 1
                                    GROUP BY mes
                                    ORDER BY n_mes asc");
   
        foreach ($participantes as $value) {
            $label[] = $value->mes;
            $data[] = $value->cantidad;
        }
        $label = json_encode($label);
        $data = json_encode($data);
        //----------------------------------------------------------

        $valores = DB::select("select count(*) As cantidad, o.descripcion
                                    FROM encuestas_resultados_opciones ero
                                        inner join encuestas_resultados er on er.id = ero.encuestas_resultados_id
                                        INNER JOIN encuestas as en ON en.id = er.encuestas_id 
                                        inner join opciones o on o.id = ero.opciones_id 
                                        INNER JOIN periodos as p ON p.encuestas_id = er.encuestas_id
                                        INNER JOIN empresas as e ON e.id = en.empresas_id 
                                                        and e.id = " . session('empresa')->id .
                                    "  WHERE p.desde <= DATE_FORMAT(CURDATE(), '%Y-%m-%d')
                                        AND p.hasta >= DATE_FORMAT(CURDATE(), '%Y-%m-%d')
                                        AND p.habilitada = 1
                                        AND en.habilitada = 1
                                    GROUP BY o.descripcion
                                    ORDER BY o.descripcion");
    
        foreach ($valores as $value) {
            $opciones_label[] = $value->descripcion;
            $opciones_data[] = $value->cantidad;
        }
        $opciones_label = json_encode($opciones_label);
        $opciones_data = json_encode($opciones_data);
        //----------------------------------------------------------

        $valores = DB::select("select
                                    max(u2.last_name) as last_name, u2.email as email, 
                                    max(g.descripcion) cargo, max(u2.area) area,
                                    count(*) cant, sum(erp.puntos) puntos
                                from 
                                    encuestas_resultados er
                                    inner join (    
                                    select  rg.users_id, rg.encuestas_resultados_id
                                    from resultados_grupal rg
                                    union all
                                    select  ri.users_id, ri.encuestas_resultados_id
                                    from resultados_individual ri
                                    ) votados
                                    on votados.encuestas_resultados_id = er.id
                                    inner join users u2 on u2.id = votados.users_id
                                    left outer join users u3 on u3.id = u2.jefe_user_id
                                    left outer join grupal g on g.id = u2.grupal_id
                                    INNER JOIN encuestas as en ON en.id = er.encuestas_id 
                                    INNER JOIN periodos as p ON p.encuestas_id = en.id
                                    INNER JOIN empresas as e ON e.id = en.empresas_id
                                            and e.id = " . session('empresa')->id .
                                    "  INNER JOIN (select sum(puntos) puntos, encuestas_resultados_id
                                            from encuestas_resultados_opciones
                                            group by encuestas_resultados_id) erp on erp.encuestas_resultados_id = er.id
                                WHERE p.desde <= DATE_FORMAT(CURDATE(), '%Y-%m-%d')
                                    AND p.hasta >= DATE_FORMAT(CURDATE(), '%Y-%m-%d')
                                    AND p.habilitada = 1
                                    AND en.habilitada = 1
                                group by u2.email");    
        

        return view('varios.dashboard', compact('data', 'label', 'opciones_label', 'opciones_data', 'valores'));
    }
}
