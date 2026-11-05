<?php

namespace Tests\Feature\Llamaparse;

use App\Models\DocumentoLlamaparse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class RegenerarJsonDocumentosLlamaparseTest extends TestCase
{
    use RefreshDatabase;

    public function test_regenera_el_json_desde_el_xlsx_guardado(): void
    {
        Storage::fake('local');

        $ruta = 'llamaparse/uuid/resultado.xlsx';
        Storage::disk('local')->put($ruta, file_get_contents($this->crearXlsx()));

        $documento = DocumentoLlamaparse::create([
            'uuid'            => '77777777-7777-7777-7777-777777777777',
            'archivo'         => 'llamaparse/uuid/documento.pdf',
            'nombre_original' => 'OP julio.pdf',
            'mime'            => 'application/pdf',
            'estado'          => 'procesado',
            'xlsx_archivo'    => $ruta,
            // JSON viejo: sin las retenciones de la hoja "Valores".
            'json'            => [
                'documento' => ['tipo' => 'detalle_liquidacion'],
                'cabecera'  => [],
                'atenciones' => [],
            ],
        ]);

        $this->artisan('documentos-llamaparse:regenerar-json', [
            '--documento' => $documento->id,
        ])->assertSuccessful();

        $documento->refresh();
        $cabecera = $documento->json['cabecera'];

        $this->assertSame(130395131, $cabecera['retencion_caja_centavos']);
        $this->assertSame(286869287, $cabecera['retencion_gastos_11_centavos']);
        $this->assertSame(130395131, $cabecera['retencion_gastos_5_centavos']);
        // Esta orden de pago no informa IB.
        $this->assertArrayNotHasKey('retencion_ib_centavos', $cabecera);
    }

    public function test_no_hace_nada_si_no_hay_documentos_procesados(): void
    {
        Storage::fake('local');

        $this->artisan('documentos-llamaparse:regenerar-json')
            ->expectsOutputToContain('No hay documentos procesados con XLSX guardado.')
            ->assertSuccessful();
    }

    private function crearXlsx(): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 1 - A');
        $hoja->fromArray([
            ['Agente', 'CUIT', 'Dirección', 'Fecha'],
            ['PIGERL GERARDO GABRIEL', '20-12345678-6', 'BUENOS AIRES 1424', '14/10/2026'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 1 - C');
        $hoja->fromArray([
            ['Valor', 'Banco', 'Numero', 'Fecha', 'Importe'],
            ['Caja de Prof. de la salud', 'S/D', null, null, '1303951.31'],
            ['Ch/ Nac. 562', 'NACION ARGENTINA', '302733', '30/12/2026', '5000000.00'],
            ['Gastos Administrativos 11%', 'S/D', null, null, '2868692.87'],
            ['Gastos Administrativos 5%', 'S/D', null, null, '1303951.31'],
        ]);

        $ruta = storage_path('app/private/llamaparse-regenerar-test-'.uniqid().'.xlsx');
        (new Xlsx($spreadsheet))->save($ruta);

        return $ruta;
    }
}
