<?php

namespace Tests\Feature\Llamaparse;

use App\Services\LlamaParse\XlsxJsonService;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class LlamaParseXlsxTest extends TestCase
{
    public function test_procesa_el_xlsx_de_llamaparse_con_hojas_de_continuacion(): void
    {
        $ruta = $this->crearXlsxLlamaParse();

        $json = app(XlsxJsonService::class)->documento($ruta, 'llamaparse.xlsx');
        @unlink($ruta);

        $this->assertSame('detalle_liquidacion', $json['documento']['tipo']);
        $this->assertSame('PIGERL GERARDO GABRIEL', $json['cabecera']['profesional']);
        $this->assertSame('20123456786', $json['cabecera']['cuit']);
        $this->assertTrue($json['cabecera']['cuit_valido']);
        $this->assertSame('Monotributo', $json['cabecera']['condicion_iva']);
        $this->assertSame('04/08/2026', $json['documento']['fecha']);
        $this->assertSame(7, $json['documento']['paginas']);
        $this->assertEquals(1800421.30, (float) $json['documento']['total_informado']);
        $this->assertTrue($json['resumen']['total_ok']);

        $sumaDetalles = array_sum(array_map(
            fn (array $atencion) => array_sum(array_column($atencion['detalles'], 'importe')),
            $json['atenciones']
        ));

        $this->assertEquals(1800421.30, round($sumaDetalles, 2));
        $this->assertEquals(round($sumaDetalles, 2), (float) $json['documento']['total_informado']);

        $this->assertCount(4, $json['atenciones']);
        $this->assertSame(11, $json['resumen']['detalles']);

        $numeros = array_column($json['atenciones'], 'atencion');
        $this->assertContains('1180759', $numeros);
        $this->assertContains('1186284', $numeros);
        $this->assertContains('1181498', $numeros);
        $this->assertContains('1181574', $numeros);

        $this->assertSame(9002107, $json['cabecera']['retencion_caja_centavos']);
        $this->assertSame(19804634, $json['cabecera']['retencion_gastos_11_centavos']);
        $this->assertSame(9002107, $json['cabecera']['retencion_gastos_5_centavos']);
        $this->assertSame(37808848, $json['cabecera']['total_retenciones_centavos']);
        $this->assertSame(142233282, $json['cabecera']['transferencia_centavos']);
    }

    public function test_toma_el_numero_de_la_orden_de_pago_del_markdown(): void
    {
        $ruta = $this->crearXlsxLlamaParse();

        $markdown = [
            [
                'page'     => 1,
                'markdown' => '## **ORDEN DE PAGO** **OPL X 0000-00077740**',
            ],
        ];

        $conMarkdown = app(XlsxJsonService::class)->documento($ruta, 'llamaparse.xlsx', $markdown);
        $sinMarkdown = app(XlsxJsonService::class)->documento($ruta, 'llamaparse.xlsx');
        @unlink($ruta);

        // El XLSX de tablas no trae el titulo del comprobante: sin markdown el
        // numero queda vacio y la auditoria caia en "DOC-<id>".
        $this->assertNull($sinMarkdown['documento']['numero']);
        $this->assertSame('000000077740', $conMarkdown['documento']['numero']);
    }

    public function test_procesa_el_xlsx_de_llamaparse_formato_orden_de_pago(): void
    {
        $ruta = $this->crearXlsxLlamaParseOrdenPago();

        $json = app(XlsxJsonService::class)->documento($ruta, 'espinola.xlsx');
        @unlink($ruta);

        $this->assertSame('orden_pago', $json['documento']['tipo']);
        $this->assertSame('555', $json['documento']['numero']);
        $this->assertSame('ESPINOLA TEST', $json['cabecera']['profesional']);
        $this->assertSame('123456', $json['cabecera']['profesional_codigo']);
        $this->assertSame('555', $json['cabecera']['numero_liquidacion']);
        $this->assertEquals(1750.0, (float) $json['documento']['total_informado']);
        $this->assertEquals(1750.0, (float) $json['documento']['total_calculado']);
        $this->assertTrue($json['resumen']['total_ok']);
        $this->assertSame(3, $json['documento']['paginas']);

        $this->assertCount(2, $json['atenciones']);
        $this->assertSame(4, $json['resumen']['detalles']);

        $primera = $json['atenciones'][0];
        $this->assertSame('1213831', $primera['atencion']);
        $this->assertSame('12/08/2026', $primera['fecha']);
        $this->assertSame('61414749102', $primera['numero_afiliado']);
        $this->assertSame('REVIGLIO, TOMAS', $primera['nombre_afiliado']);
        $this->assertSame('OSDE', $primera['obra_social']);
        $this->assertSame('210 GRAVADO', $primera['plan']);
        $this->assertCount(3, $primera['detalles']);
        $this->assertEquals(1500.0, (float) $primera['total_atencion']);
        $this->assertEquals(1500.0, (float) $primera['total_calculado']);
        $this->assertSame([], $primera['advertencias']);

        $codigos = array_column($primera['detalles'], 'codigo_nivel');
        $this->assertSame(['600023', '600004', '600024'], $codigos);
        $this->assertSame(100.0, (float) $primera['detalles'][0]['porcentaje_cobertura']);
        $this->assertSame(1.0, (float) $primera['detalles'][0]['cantidad']);
        $this->assertEquals(300.0, (float) $primera['detalles'][0]['importe']);
        $this->assertSame(2, $primera['detalles'][0]['pagina']);
        $this->assertSame(3, $primera['detalles'][2]['pagina']);

        $segunda = $json['atenciones'][1];
        $this->assertSame('1219999', $segunda['atencion']);
        $this->assertSame('BASSI, MATIAS', $segunda['nombre_afiliado']);
        $this->assertCount(1, $segunda['detalles']);
        $this->assertEquals(250.0, (float) $segunda['detalles'][0]['importe']);

        $this->assertSame(8750, $json['cabecera']['retencion_caja_centavos']);
        $this->assertSame(19250, $json['cabecera']['retencion_gastos_11_centavos']);
        $this->assertSame(8750, $json['cabecera']['retencion_gastos_5_centavos']);
        $this->assertSame(36750, $json['cabecera']['total_retenciones_centavos']);
        $this->assertSame(138250, $json['cabecera']['transferencia_centavos']);
    }

    public function test_extrae_el_cuit_de_la_tabla_agente_cuando_la_hoja_no_se_llama_page(): void
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('--ORDEN DE PAGO---1');
        $hoja->fromArray([
            ['Agente', 'CUIT', 'Dirección', 'Fecha'],
            ['FRETES HERNAN MILTON', '20-29704764-8', 'ALSINA 249', '14/10/2026'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('--Detalle Liquidació-6');
        $hoja->fromArray([
            ['Atención', 'Fecha', 'Paciente', 'Obra Social', 'Periodo', 'Tipo Nom.', 'Código', 'Descripción', 'Valor liq.'],
            ['1222181', '28/08/2026', 'ESPANON,', '91 - OBRA SOCIAL', '202608', 'ANESTESIA', '600023', 'Evaluación', '37,372.12'],
        ]);

        $ruta = storage_path('app/private/llamaparse-agente-test-'.uniqid().'.xlsx');
        (new Xlsx($spreadsheet))->save($ruta);

        $json = app(XlsxJsonService::class)->documento($ruta, 'orden.pdf');
        @unlink($ruta);

        $this->assertSame('FRETES HERNAN MILTON', $json['cabecera']['profesional']);
        $this->assertSame('20297047648', $json['cabecera']['cuit']);
        $this->assertTrue($json['cabecera']['cuit_valido']);
        $this->assertSame('14/10/2026', $json['documento']['fecha']);
    }

    private function crearXlsxLlamaParseOrdenPago(): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 1 - A');
        $hoja->fromArray([
            ['Comprobante', 'Fecha', 'Nº Liq.', 'Observaciones', 'Neto', 'IVA', 'Total', 'Cancela', 'Saldo'],
            ['LIQ L 0000-00114379', '15/09/2026', '97684', 'OSDE- ANESTESIA', '1,750.00', '0.00', '1,750.00', '1,750.00', '0.00'],
            [null, null, null, null, null, null, null, '1,750.00'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 1 - B');
        $hoja->fromArray([
            ['Valor', 'Banco', 'Numero', 'Fecha', 'Importe'],
            ['Caja de Prof. de la salud', 'S/D', null, null, '87.50'],
            ['Gastos Administrativos 11%', 'S/D', null, null, '192.50'],
            ['Gastos Administrativos 5%', 'S/D', null, null, '87.50'],
            ['Transf. Bco. Prov. 5363810', 'PROVINCIA DE BS.AS.', '1', '16/09/2026', '1,382.50'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 2 - A');
        $hoja->fromArray([
            ['Fecha Práctica', 'Cod. Nom.', 'Desc. Nom.', 'Concepto', 'Prestador', 'IVA', 'Total'],
            ['Agente: 123456-ESPINOLA TEST'],
            ['Liquidación:', '555'],
            ['Atención: 1213831<br/>Nº Afiliado: 61414749102', 'Fecha: 12/08/2026<br/>Nombre Afiliado: REVIGLIO, TOMAS', 'Area: I', 'Obra Social - Plan: OSDE - 210 GRAVADO'],
            ['15/08/2026 600023 Evaluación', 'Esp', '123456-ESPINOLA TEST'],
            ['100% APM SEGUN PORC. COBERTURA PRESENT.', '1.00', '300.00', '0.00', '300.00'],
            ['15/08/2026 600004 Mayor F', 'Esp', '123456-ESPINOLA TEST'],
            ['100% APM SEGUN PORC. COBERTURA PRESENT.', '1.00', '700.00', '0.00', '700.00'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 3 - A');
        $hoja->fromArray([
            ['Fecha Práctica', 'Cod. Nom.', 'Desc. Nom.', 'Concepto', 'Prestador', 'IVA', 'Total'],
            ['15/08/2026', '600024', 'Urgencia diurna', 'Esp', '123456-ESPINOLA TEST'],
            ['100% APM SEGUN PORC. COBERTURA PRESENT.', null, null, '1.00', '500.00', '0.00', '500.00'],
            ['Total Atención', null, null, null, '1,500.00', '0.00', '1,500.00'],
            ['Atención:', '1219999', 'Fecha: 20/08/2026'],
            ['Nº Afiliado:', '62428679601', 'Nombre Afiliado: BASSI, MATIAS', 'Area: I', 'Obra Social - Plan: OSDE - 210 NO GRAVADO'],
            ['20/08/2026', '600023', 'Evaluación', 'Esp', '123456-ESPINOLA TEST'],
            ['100% APM SEGUN PORC. COBERTURA PRESENT.', null, null, '1.00', '250.00', '0.00', '250.00'],
            ['Total Atención', null, null, null, '250.00', '0.00', '250.00'],
            ['Total Liquidación', null, null, null, '1,750.00', '0.00', '1,750.00'],
        ]);

        $ruta = storage_path('app/private/llamaparse-orden-pago-test-'.uniqid().'.xlsx');
        (new Xlsx($spreadsheet))->save($ruta);

        return $ruta;
    }

    public function test_distingue_y_procesa_ambos_formatos_en_el_mismo_archivo(): void
    {
        $ruta = $this->crearXlsxMixto();

        $json = app(XlsxJsonService::class)->documento($ruta, 'mixto.xlsx');
        @unlink($ruta);

        $this->assertSame('detalle_liquidacion', $json['documento']['tipo']);
        $this->assertCount(2, $json['atenciones']);
        $this->assertSame(2, $json['resumen']['detalles']);
        $this->assertEquals(2000.0, (float) $json['documento']['total_informado']);
        $this->assertEquals(2000.0, (float) $json['documento']['total_calculado']);
        $this->assertTrue($json['resumen']['total_ok']);

        $numeros = array_column($json['atenciones'], 'atencion');
        $this->assertContains('1213831', $numeros);
        $this->assertContains('1180759', $numeros);
    }

    private function crearXlsxMixto(): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 1 - A');
        $hoja->fromArray([
            ['Comprobante', 'Fecha', 'Nº Liq.', 'Observaciones', 'Neto', 'IVA', 'Total', 'Cancela', 'Saldo'],
            ['LIQ L 0000-00120000', '15/09/2026', '777', 'MIXTO', '2,000.00', '0.00', '2,000.00', '2,000.00', '0.00'],
            [null, null, null, null, null, null, null, '2,000.00'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 2 - A');
        $hoja->fromArray([
            ['Fecha Práctica', 'Cod. Nom.', 'Desc. Nom.', 'Concepto', 'Prestador', 'IVA', 'Total'],
            ['Agente: 123456-ESPINOLA TEST'],
            ['Liquidación:', '777'],
            ['Atención: 1213831<br/>Nº Afiliado: 61414749102', 'Fecha: 12/08/2026<br/>Nombre Afiliado: REVIGLIO, TOMAS', 'Area: I', 'Obra Social - Plan: OSDE - 210 GRAVADO'],
            ['15/08/2026 600023 Evaluación', 'Esp', '123456-ESPINOLA TEST'],
            ['100% APM SEGUN PORC. COBERTURA PRESENT.', '1.00', '1,000.00', '0.00', '1,000.00'],
            ['Total Atención', null, null, null, '1,000.00'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 3 - A');
        $hoja->fromArray([
            ['Atención', 'Fecha', 'Paciente', 'Obra Social', 'Periodo', 'Tipo Nom.', 'Código', 'Descripción', 'Valor liq.'],
            ['1180759', '11/06/2026', 'SANCHEZ, CARLOS', '143 - UOM', '202606', 'ANESTESIA', '600011', 'Especial F', '1,000.00'],
        ]);

        $ruta = storage_path('app/private/llamaparse-mixto-test-'.uniqid().'.xlsx');
        (new Xlsx($spreadsheet))->save($ruta);

        return $ruta;
    }

    private function crearXlsxLlamaParse(): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 1 - A');
        $hoja->fromArray([
            ['Agente', 'CUIT', 'Dirección', 'Fecha'],
            ['PIGERL GERARDO GABRIEL', '20-12345678-6', 'BUENOS AIRES 1424', '04/08/2026'],
            ['Cond. IVA'],
            ['Monotributo'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 1 - B');
        $hoja->fromArray([
            ['Comprobante', 'Fecha', 'Nº Liq.', 'Observaciones', 'Neto', 'IVA', 'Total', 'Cancela', 'Saldo'],
            ['LIQ L 0000-00110340', '13/07/2026', '94326', 'HONORARIOS', '1,800,421.30', '0.00', '1,800,421.30', '1,800,421.30', '0.00'],
            [null, null, null, null, null, null, null, '1,800,421.30'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 1 - C');
        $hoja->fromArray([
            ['Valor', 'Banco', 'Numero', 'Fecha', 'Importe'],
            ['Caja de Prof. de la salud', 'S/D', null, null, '90,021.07'],
            ['Ch/ Nac. 562', 'NACION ARGENTINA', '299871', '30/10/2026', '1,400,000.00'],
            ['Ch/ Nac. 562', 'NACION ARGENTINA', '299872', '09/10/2026', '22,332.82'],
            ['Gastos Administrativos 11%', 'S/D', null, null, '198,046.34'],
            ['Gastos Administrativos 5%', 'S/D', null, null, '90,021.07'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 3 - A');
        $hoja->fromArray([
            ['Atención', 'Fecha', 'Paciente', 'Obra Social', 'Periodo', 'Tipo Nom.', 'Código', 'Descripción', 'Valor liq.'],
            ['1180759', '11/06/2026', 'SANCHEZ, CARLOS', '143 - UOM', '202606', 'ANESTESIA', '600011', 'Especial F', '478,291.88'],
            ['1180759', '11/06/2026', 'SANCHEZ, CARLOS', '143 - UOM', '202606', 'ANESTESIA', '600023', 'Evaluación', '35,436.53'],
            ['1186284', '22/06/2026', 'ABALOS, EDMUNDO', '143 - UOM', '202606', 'ANESTESIA', '600012', 'Especial G', '549,652.90'],
            ['1186284', '22/06/2026', 'ABALOS, EDMUNDO', '143 - UOM', '202606', 'ANESTESIA', '600010', 'Especial E', '207,804.05'],
            ['1186284', '22/06/2026', 'ABALOS, EDMUNDO', '143 - UOM', '202606', 'ANESTESIA', '600002', 'Mayor D', '135,847.42'],
            ['1186284', '22/06/2026', 'ABALOS, EDMUNDO', '143 - UOM', '202606', 'ANESTESIA', '600023', 'Evaluación', '35,436.53'],
            ['Total Liquidación N° L 0000 - 00110340', null, null, null, null, null, null, null, '1,442,469.31'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 7 - B');
        $hoja->fromArray([
            ['1181498', '12/06/2026', 'BUSTOS, MARTA', '124 - PAMI', '202606', 'ANESTESIA', '600023', 'Evaluación', '28,632.01'],
            ['1181498', '12/06/2026', 'BUSTOS, MARTA', '124 - PAMI', '202606', 'ANESTESIA', '600001', 'Mayor B', '95,464.49'],
            ['1181574', '12/06/2026', 'FORCADA,', '124 - PAMI', '202606', 'ANESTESIA', '600023', 'Evaluación', '28,632.01'],
            ['1181574', '12/06/2026', 'FORCADA,', '124 - PAMI', '202606', 'ANESTESIA', '600002', 'Mayor D', '109,758.99'],
            ['1181574', '12/06/2026', 'FORCADA,', '124 - PAMI', '202606', 'ANESTESIA', '600001', 'Mayor B', '95,464.49'],
            ['Total Liquidación N° L 0000 - 00111591', null, null, null, null, null, null, null, '357,951.99'],
        ]);

        $ruta = storage_path('app/private/llamaparse-test-'.uniqid().'.xlsx');
        (new Xlsx($spreadsheet))->save($ruta);

        return $ruta;
    }
}
