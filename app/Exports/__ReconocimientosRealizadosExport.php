<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
// use App\Models\Encuesta;

class ReconocimientosRealizadosExport implements FromCollection, WithHeadings, WithStyles
{

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if (empty(session('query_reconocimientos'))) {
            $coleccion = collect();
        } else {
            $query = session('query_reconocimientos');
            $campos = 'enc_desc, last_name, observaciones, puntos, fecha_ingreso, opciones_concat, votados_concat';
            $query = str_replace('*', $campos, $query);

            $resu = DB::select($query);
            $coleccion = collection::make($resu);
        }
        return $coleccion;
        //return encuesta::all();
    }

    public function headings(): array
    {
        // Agregar los nombres de las columnas aquí
        return ['encuesta', 'voto', 'motivo', 'puntos', 'fecha', 'opciones', 'votados'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => '70ACF5', // Color de fondo de los encabezados
                ],
            ],
        ]);
    }
}
