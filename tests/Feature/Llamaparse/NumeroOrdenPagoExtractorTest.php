<?php

namespace Tests\Feature\Llamaparse;

use App\Services\LlamaParse\NumeroOrdenPagoExtractor;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NumeroOrdenPagoExtractorTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function formatos(): array
    {
        return [
            'opl con x mayuscula'    => ['## **ORDEN DE PAGO** **OPL X 0000-00077740**', '000000077740'],
            'opl en minuscula'       => ['orden de pago opl x 0000-00077740', '000000077740'],
            'orden de pago con nro'  => ['ORDEN DE PAGO Nº 0000-00077740', '000000077740'],
            'op con numero corto'    => ['OP Nº 77740', '77740'],
            'opl en una tabla html'  => ['<td>OPL X 0000-00077740</td>', '000000077740'],
        ];
    }

    #[DataProvider('formatos')]
    public function test_extrae_el_numero_de_la_orden_de_pago(string $markdown, string $esperado): void
    {
        $this->assertSame($esperado, NumeroOrdenPagoExtractor::desdeMarkdown($markdown));
    }

    public function test_recorre_las_paginas_del_markdown(): void
    {
        $markdown = [
            [
                'page'     => 1,
                'markdown' => '# DETALLE DE LIQUIDACION',
                'items'    => [['type' => 'text', 'value' => 'Sin numero de OP']],
            ],
            [
                'page'     => 2,
                'markdown' => "## **ORDEN DE PAGO** **OPL X 0000-00077740**\n\n**Importe: 26079026.11**",
            ],
        ];

        $this->assertSame('000000077740', NumeroOrdenPagoExtractor::desdeMarkdown($markdown));
    }

    public function test_no_inventa_un_numero_que_no_existe(): void
    {
        $this->assertNull(NumeroOrdenPagoExtractor::desdeMarkdown(null));
        $this->assertNull(NumeroOrdenPagoExtractor::desdeMarkdown(''));
        $this->assertNull(NumeroOrdenPagoExtractor::desdeMarkdown('DETALLE DE LIQUIDACION'));
        $this->assertNull(NumeroOrdenPagoExtractor::desdeMarkdown(['page' => 1, 'markdown' => 'LIQ L 0000-00114228']));
    }
}
