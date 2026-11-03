<?php

namespace Tests\Feature\Llamaparse;

use App\Services\LlamaParse\DocumentoValidator;
use Tests\TestCase;

class DocumentoValidatorTest extends TestCase
{
    public function test_valida_y_normaliza_una_extraccion_completa(): void
    {
        $resultado = app(DocumentoValidator::class)->validar([
            'documento' => [
                'tipo' => 'parte',
                'numero' => 'P-001',
                'fecha' => '01/03/2026',
                'paginas' => '2',
            ],
            'cabecera' => [
                'profesional_codigo' => 'AG-001',
                'profesional' => 'Dr. Test',
                'concepto' => 'Caja de Prof. de la Salud',
                'importe' => '558.4OO,44',
                'importe_original' => '558.4OO,44',
            ],
            'atenciones' => [
                [
                    'obra_social' => 'OS Test',
                    'plan' => 'Plan 1000',
                    'atencion' => '0000123456',
                    'fecha' => '01/03/2026',
                    'numero_afiliado' => '87654321',
                    'nombre_afiliado' => 'Paciente Test',
                    'detalles' => [
                        [
                            'fecha' => '01/03/2026',
                            'codigo_nivel' => '001',
                            'descripcion_nivel' => 'Practica',
                            'periodo' => '08/2026',
                            'porcentaje_cobertura' => '100%',
                            'cantidad' => '2',
                            'importe' => '1.234,56',
                            'pagina' => '1',
                        ],
                    ],
                ],
            ],
            'confianza' => 85,
            'observaciones' => null,
        ]);

        $this->assertSame(2, $resultado['documento']['paginas']);
        $this->assertSame(0.85, $resultado['confianza']);
        $this->assertSame('AG-001', $resultado['cabecera']['profesional_codigo']);
        $this->assertSame('Caja de Prof. de la Salud', $resultado['cabecera']['concepto']);
        $this->assertSame(558400.44, $resultado['cabecera']['importe']);
        $this->assertFalse($resultado['cabecera']['importe_revisar']);
        $this->assertSame('0000123456', $resultado['atenciones'][0]['atencion']);
        $this->assertSame('01/03/2026', $resultado['atenciones'][0]['fecha']);
        $this->assertSame('87654321', $resultado['atenciones'][0]['numero_afiliado']);
        $this->assertSame('Paciente Test', $resultado['atenciones'][0]['nombre_afiliado']);
        $this->assertSame('08/2026', $resultado['atenciones'][0]['detalles'][0]['periodo']);
        $this->assertSame(100.0, $resultado['atenciones'][0]['detalles'][0]['porcentaje_cobertura']);
        $this->assertSame(2.0, $resultado['atenciones'][0]['detalles'][0]['cantidad']);
        $this->assertSame(1234.56, $resultado['atenciones'][0]['detalles'][0]['importe']);
        $this->assertSame(1, $resultado['atenciones'][0]['detalles'][0]['pagina']);
        $this->assertNull($resultado['observaciones']);
    }

    public function test_corrige_ocr_en_campos_numericos_y_guarda_el_original(): void
    {
        $resultado = app(DocumentoValidator::class)->validar([
            'documento' => [
                'numero' => 'l2O',
                'numero_original' => 'l2O',
            ],
            'cabecera' => [],
            'atenciones' => [
                [
                    'atencion' => 'I2O',
                    'atencion_original' => 'I2O',
                    'numero_afiliado' => 'S8G',
                    'numero_afiliado_original' => 'S8G',
                    'detalles' => [
                        [
                            'cantidad' => 'I',
                            'cantidad_original' => 'I',
                            'porcentaje_cobertura' => 'IOO',
                            'porcentaje_cobertura_original' => 'IOO',
                            'importe' => 'I2O',
                            'importe_original' => 'I2O',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame('120', $resultado['documento']['numero']);
        $this->assertSame('120', $resultado['atenciones'][0]['atencion']);
        $this->assertSame('I2O', $resultado['atenciones'][0]['atencion_original']);
        $this->assertFalse($resultado['atenciones'][0]['atencion_revisar']);
        $this->assertSame('586', $resultado['atenciones'][0]['numero_afiliado']);
        $this->assertFalse($resultado['atenciones'][0]['numero_afiliado_revisar']);

        $detalle = $resultado['atenciones'][0]['detalles'][0];
        $this->assertSame(1.0, $detalle['cantidad']);
        $this->assertSame(100.0, $detalle['porcentaje_cobertura']);
        $this->assertSame(120.0, $detalle['importe']);
        $this->assertSame('IOO', $detalle['porcentaje_cobertura_original']);
    }

    public function test_marca_revisar_cuando_quedan_caracteres_no_convertibles(): void
    {
        $resultado = app(DocumentoValidator::class)->validar([
            'cabecera' => [],
            'atenciones' => [
                [
                    'atencion' => '12X',
                    'atencion_original' => '12X',
                    'detalles' => [],
                ],
            ],
        ]);

        $this->assertSame('12', $resultado['atenciones'][0]['atencion']);
        $this->assertTrue($resultado['atenciones'][0]['atencion_revisar']);
    }

    public function test_corrige_atencion_17_a_11_y_marca_revisar(): void
    {
        $resultado = app(DocumentoValidator::class)->validar([
            'cabecera' => [],
            'atenciones' => [
                [
                    'atencion' => '1775584',
                    'detalles' => [],
                ],
            ],
        ]);

        $this->assertSame('1175584', $resultado['atenciones'][0]['atencion']);
        $this->assertSame('1775584', $resultado['atenciones'][0]['atencion_original']);
        $this->assertTrue($resultado['atenciones'][0]['atencion_revisar']);
        $this->assertStringContainsString('17... a 11...', implode(' ', $resultado['atenciones'][0]['advertencias']));
    }

    public function test_marca_nombre_truncado_y_total_que_no_coincide(): void
    {
        $resultado = app(DocumentoValidator::class)->validar([
            'cabecera' => [],
            'atenciones' => [
                [
                    'atencion' => '1175584',
                    'nombre_afiliado' => 'SANGIACOMO,',
                    'total_atencion' => 100,
                    'detalles' => [
                        ['importe' => 50],
                        ['importe' => 20],
                    ],
                ],
            ],
        ]);

        $advertencias = implode(' ', $resultado['atenciones'][0]['advertencias']);
        $this->assertStringContainsString('truncado', $advertencias);
        $this->assertStringContainsString('no coincide', $advertencias);
        $this->assertSame(70.0, $resultado['atenciones'][0]['total_calculado']);
    }

    public function test_consolida_misma_atencion_con_misma_fecha_paciente_y_obra_social(): void
    {
        $detalle = fn (string $codigo) => [
            'codigo_nivel' => $codigo,
            'descripcion_nivel' => 'Practica',
            'importe' => 10,
            'pagina' => 1,
        ];

        $resultado = app(DocumentoValidator::class)->validar([
            'cabecera' => [],
            'atenciones' => [
                [
                    'atencion' => '1186284',
                    'fecha' => '01/03/2026',
                    'nombre_afiliado' => 'ABALOS, EDMUNDO',
                    'obra_social' => 'UOM',
                    'detalles' => [$detalle('600012')],
                ],
                [
                    'atencion' => '1186284',
                    'fecha' => '01/03/2026',
                    'nombre_afiliado' => 'ABALOS, EDMUNDO',
                    'obra_social' => 'UOM',
                    'detalles' => [$detalle('600010')],
                ],
                [
                    'atencion' => '1186284',
                    'fecha' => '01/03/2026',
                    'nombre_afiliado' => 'OTRO PACIENTE',
                    'obra_social' => 'UOM',
                    'detalles' => [$detalle('600002')],
                ],
            ],
        ]);

        $this->assertCount(2, $resultado['atenciones']);
        $this->assertCount(2, $resultado['atenciones'][0]['detalles']);
        $this->assertSame(1, $resultado['atenciones'][0]['bloque_id']);
        $this->assertSame(2, $resultado['atenciones'][1]['bloque_id']);
        $this->assertSame(2, $resultado['resumen']['bloques']);
        $this->assertSame(1, $resultado['resumen']['atenciones_unicas']);
        $this->assertSame(3, $resultado['resumen']['detalles']);
    }

    public function test_suma_los_totales_al_consolidar_atenciones_repetidas(): void
    {
        $atencion = fn (float $importe) => [
            'atencion' => '1186284',
            'fecha' => '01/03/2026',
            'nombre_afiliado' => 'ABALOS, EDMUNDO',
            'obra_social' => 'UOM',
            'total_atencion' => $importe,
            'detalles' => [[
                'codigo_nivel' => '600012',
                'descripcion_nivel' => 'Practica',
                'importe' => $importe,
                'pagina' => 1,
            ]],
        ];

        $resultado = app(DocumentoValidator::class)->validar([
            'cabecera' => [],
            'atenciones' => [$atencion(100), $atencion(200)],
        ]);

        $this->assertCount(1, $resultado['atenciones']);
        $this->assertSame(300.0, (float) $resultado['atenciones'][0]['total_atencion']);
        $this->assertCount(2, $resultado['atenciones'][0]['detalles']);
        $this->assertSame([], $resultado['atenciones'][0]['advertencias']);
    }

    public function test_advierte_atencion_fuera_del_formato_esperado(): void
    {
        $resultado = app(DocumentoValidator::class)->validar([
            'cabecera' => [],
            'atenciones' => [
                [
                    'atencion' => '123',
                    'detalles' => [],
                ],
            ],
        ]);

        $this->assertStringContainsString(
            'fuera del formato esperado',
            implode(' ', $resultado['atenciones'][0]['advertencias'])
        );
    }

    public function test_detecta_total_informado_que_no_coincide(): void
    {
        $resultado = app(DocumentoValidator::class)->validar([
            'documento' => ['total_informado' => 100],
            'cabecera' => [],
            'atenciones' => [
                [
                    'atencion' => '1175584',
                    'fecha' => '01/03/2026',
                    'nombre_afiliado' => 'Paciente Test',
                    'obra_social' => 'OS Test',
                    'detalles' => [
                        ['importe' => 50],
                        ['importe' => 20],
                    ],
                ],
            ],
        ]);

        $this->assertSame(70.0, $resultado['documento']['total_calculado']);
        $this->assertFalse($resultado['resumen']['total_ok']);
        $this->assertStringContainsString('no coincide', implode(' ', $resultado['advertencias']));
    }

    public function test_no_fusiona_atenciones_repetidas_y_conserva_todos_los_bloques(): void
    {
        $detalle = fn (string $codigo) => [
            'fecha' => '01/03/2026',
            'codigo_nivel' => $codigo,
            'descripcion_nivel' => 'Practica',
            'porcentaje_cobertura' => 100,
            'cantidad' => 1,
            'importe' => 1000,
            'pagina' => 1,
        ];

        $resultado = app(DocumentoValidator::class)->validar([
            'cabecera' => [],
            'atenciones' => [
                [
                    'atencion' => '1',
                    'obra_social' => 'OS Test',
                    'detalles' => [$detalle('001')],
                ],
                [
                    'atencion' => '3',
                    'detalles' => [$detalle('002')],
                ],
                [
                    'atencion' => '1',
                    'numero_afiliado' => '87654321',
                    'detalles' => [$detalle('003'), $detalle('004')],
                ],
            ],
        ]);

        $this->assertCount(3, $resultado['atenciones']);
        $this->assertSame('1', $resultado['atenciones'][0]['atencion']);
        $this->assertSame('OS Test', $resultado['atenciones'][0]['obra_social']);
        $this->assertCount(1, $resultado['atenciones'][0]['detalles']);
        $this->assertSame('3', $resultado['atenciones'][1]['atencion']);
        $this->assertSame('1', $resultado['atenciones'][2]['atencion']);
        $this->assertSame('87654321', $resultado['atenciones'][2]['numero_afiliado']);
        $this->assertCount(2, $resultado['atenciones'][2]['detalles']);
        $this->assertSame([1, 2, 3], array_column($resultado['atenciones'], 'bloque_id'));
    }

    public function test_acepta_una_extraccion_sin_secciones_opcionales(): void
    {
        $resultado = app(DocumentoValidator::class)->validar([
            'cabecera' => [],
        ]);

        $this->assertSame([], $resultado['atenciones']);
        $this->assertNull($resultado['confianza']);
    }

    public function test_la_confianza_fuera_de_rango_genera_advertencia_sin_abortar(): void
    {
        $resultado = app(DocumentoValidator::class)->validar([
            'documento' => [],
            'cabecera' => [],
            'atenciones' => [],
            'confianza' => 101,
        ]);

        $this->assertSame(101.0, $resultado['confianza']);
        $this->assertNotEmpty($resultado['advertencias']);
    }
}
