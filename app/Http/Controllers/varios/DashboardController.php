<?php

namespace App\Http\Controllers\varios;

use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function show() {

        $participantes = DB::select("SELECT COUNT(*) AS cantidad, 
                                            MAX(DATE_FORMAT(created_at, '%m')) AS n_mes, 
                                            DATE_FORMAT(created_at, '%b') AS mes
                                        FROM encuestas_resultados
                                        GROUP BY DATE_FORMAT(created_at, '%b')
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
                                        inner join opciones o on o.id = ero.opciones_id 
                                    GROUP BY o.descripcion
                                    ORDER BY o.descripcion");
    
        foreach ($valores as $value) {
            $opciones_label[] = $value->descripcion;
            $opciones_data[] = $value->cantidad;
        }
        $opciones_label = json_encode($opciones_label);
        $opciones_data = json_encode($opciones_data);
        //----------------------------------------------------------

        return view('varios.dashboard', compact('data', 'label', 'opciones_label', 'opciones_data'));
    }
}
