<?php

namespace App\Http\Controllers\varios;

use App\Http\Controllers\Controller;
use App\Models\encuesta_resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\user;

class DashboardController extends Controller
{

    public function show()
    {
        $periodo = DB::select("SELECT desde, hasta
                                    FROM periodos as p
                                        INNER JOIN encuestas as en ON en.id = p.encuestas_id
                                        INNER JOIN empresas as e ON e.id = en.empresas_id
                                                    and e.id = " . session('empresa')->id .
                                    " and desde  <= DATE_FORMAT(CURDATE(), '%Y-%m-%d')
                                      and hasta >= DATE_FORMAT(CURDATE(), '%Y-%m-%d')
                                        AND p.habilitada = 1
                                        AND en.habilitada = 1
                        ");
        
        $desde = null;
        $hasta = null;
        if ($periodo) {
            $desde = $periodo[0]->desde;
            $hasta = $periodo[0]->hasta;
        }
        
        return $this->CargaDatos($desde, $hasta, 'actual');
    }

    public function Cambio(Request $request)
    {
        $validate = $request->validate([
            'radio-group' => 'required|in:actual,perso',
            'desde' => 'required_if:radio-group,perso|date',
            'hasta' => 'required_if:radio-group,perso|date|after_or_equal:desde',
        ], [
            'radio-group.required' => 'Debe seleccionar una opción, Actual o un Rango e fechas.',
            'radio-group.in' => 'Debe seleccionar "actual" o "Rango de fechas".',
            'desde.required_if' => 'El campo fecha Desde es obligatorio cuando selecciona Rango de fechas.',
            'desde.date' => 'El campo fecha Desde debe ser una fecha válida.',
            'hasta.required_if' => 'El campo fecha Hasta es obligatorio cuando selecciona Rango de fechas.',
            'hasta.date' => 'El campo fecha Hasta debe ser una fecha válida.',
            'hasta.after_or_equal' => 'El campo fecha Hasta debe ser mayor o igual a la fecha Desde.',
        ]);

        if( $validate['radio-group'] == 'actual'){
            return $this->show();
        }

        return $this->CargaDatos($request->desde, $request->hasta, $validate['radio-group']);

    }

    public function CargaDatos($desde, $hasta, $option) {

        $participantes = DB::select("SELECT COUNT(*) AS cantidad, 
                                    MAX(DATE_FORMAT(er.created_at, '%m')) AS n_mes, 
                                    DATE_FORMAT(er.created_at, '%b') AS mes
                                    FROM encuestas_resultados as er
                                    INNER JOIN encuestas as en ON en.id = er.encuestas_id 
                                    INNER JOIN empresas as e ON e.id = en.empresas_id
                                                    and e.id = " . session('empresa')->id .
                                    "  WHERE DATE_FORMAT('" . $desde . "', '%Y-%m-%d') <= DATE_FORMAT(er.created_at, '%Y-%m-%d')
                                        AND DATE_FORMAT('" . $hasta . "', '%Y-%m-%d') >= DATE_FORMAT(er.created_at, '%Y-%m-%d')
                                        AND en.habilitada = 1
                                    GROUP BY mes
                                    ORDER BY n_mes asc");
// dd($participantes);
// INNER JOIN periodos as p ON p.encuestas_id = en.id
// AND p.habilitada = 1

        $label = [];
        $data = [];
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
                                        INNER JOIN empresas as e ON e.id = en.empresas_id 
                                        and e.id = " . session('empresa')->id .
                                        "  WHERE DATE_FORMAT('" . $desde . "', '%Y-%m-%d') <= DATE_FORMAT(er.created_at, '%Y-%m-%d')
                                            AND DATE_FORMAT('" . $hasta . "', '%Y-%m-%d') >= DATE_FORMAT(er.created_at, '%Y-%m-%d')
                                            AND en.habilitada = 1
                                        GROUP BY o.descripcion
                                    ORDER BY o.descripcion");

        $opciones_data = [];
        $opciones_label = [];
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
                                    ) votados on votados.encuestas_resultados_id = er.id
                                    inner join users u2 on u2.id = votados.users_id
                                    left outer join users u3 on u3.id = u2.jefe_user_id
                                    left outer join grupal g on g.id = u2.grupal_id
                                    INNER JOIN encuestas as en ON en.id = er.encuestas_id 
                                    INNER JOIN empresas as e ON e.id = en.empresas_id 
                                            and e.id = " . session('empresa')->id .
                                 "  INNER JOIN (select sum(puntos) puntos, encuestas_resultados_id
                                            from encuestas_resultados_opciones
                                            group by encuestas_resultados_id) erp on erp.encuestas_resultados_id = er.id
                                    WHERE DATE_FORMAT('" . $desde . "', '%Y-%m-%d') <= DATE_FORMAT(er.created_at, '%Y-%m-%d')
                                            AND DATE_FORMAT('" . $hasta . "', '%Y-%m-%d') >= DATE_FORMAT(er.created_at, '%Y-%m-%d')
                                    AND en.habilitada = 1
                                group by u2.email");

        $cant_usu = user::where("empresas_id", session('empresa')->id)->count();
        $cant_recon = DB::select("SELECT COUNT(*) AS cantidad 
                                FROM encuestas_resultados as er
                                INNER JOIN encuestas as en ON en.id = er.encuestas_id 
                                INNER JOIN empresas as e ON e.id = en.empresas_id
                                                and e.id = " . session('empresa')->id .
                                "  WHERE DATE_FORMAT('" . $desde . "', '%Y-%m-%d') <= DATE_FORMAT(er.created_at, '%Y-%m-%d')
                                        AND DATE_FORMAT('" . $hasta . "', '%Y-%m-%d') >= DATE_FORMAT(er.created_at, '%Y-%m-%d')
                                    AND en.habilitada = 1");
        $cant_recon = $cant_recon[0]->cantidad;

        return view('varios.dashboard', compact('data', 'label', 'opciones_label', 'opciones_data', 'valores', 'cant_usu', 
                    'cant_recon', 'desde', 'hasta', 'option'));

    }
}
