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
        $resu = DB::select('select u.id, u.last_name, g.descripcion, u.area, null as country, 
                                u2.last_name as jefe_last_name, null as fec_nac, null as sede, u.email, 
                                u.telefono, u.es_jefe, null as dni, u2.email email_jefe, r.name as perfil 
                        from users as u 
                            left outer join  grupal as g on u.grupal_id = g.id
                            left outer join users as u2 on u2.id = u.jefe_user_id
                            left outer join model_has_roles as mhr on u.id = mhr.model_id
                            left outer join roles as r on r.id = mhr.role_id 
                        where u.empresas_id = ' . session('empresa')->id
                            . ' and u.deleted_at is null');
        $coleccion = collection::make($resu);

        return $coleccion;
        //return encuesta::all();
    }

    public function headings(): array
    {
        // Agregar los nombres de las columnas aquí
        return ['Employee ID', 'Legal Name', 'Job Title', 'Functional organization', 'Work Country', 
                'Manager Name', 'Date of Birth', 'Sede', 'job-mail address', 'Phone Number', 'Is boss', 'DNI', 'Boss email', 'perfil'];
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
