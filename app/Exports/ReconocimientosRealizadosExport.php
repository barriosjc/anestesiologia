<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use App\Models\Encuesta;

class ReconocimientosRealizadosExport implements FromCollection, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $resu = DB::select('select  enc_desc, last_name, observaciones, puntos, fecha_ingreso, opciones_concat, votados_concat 
                        from v_reconocimientos_realizados 
                        where empresas_id = ' . session('empresa')->id);
        $coleccion = collection::make($resu);

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
