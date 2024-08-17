<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
// use App\Models\Encuesta;

class UsuariosExport implements FromCollection, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $resu = DB::select('select u.id, u.name, u.name, g.descripcion, null as country, 
                                null as fec_nac, null as sede, u.email, 
                                u.telefono, null as dni, r.name as perfil 
                        from users as u 
                            left outer join model_has_roles as mhr on u.id = mhr.model_id
                            left outer join roles as r on r.id = mhr.role_id 
                        where u.deleted_at is null');
        $coleccion = collection::make($resu);

        return $coleccion;
        //return encuesta::all();
    }

    public function headings(): array
    {
        // Agregar los nombres de las columnas aquí
        return ['Employee ID', 'Nombre y apellido','Usuario', 'Cargo', 'Area', 'Work Country', 
                'Nombre jefe', 'Fecha de nac.', 'Sede', 'email', 'Telefono', 'Es jefe', 'DNI', 'email jefe', 'Perfil'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:N1')->applyFromArray([
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
