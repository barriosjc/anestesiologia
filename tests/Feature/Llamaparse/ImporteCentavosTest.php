<?php

namespace Tests\Feature\Llamaparse;

use App\Services\LlamaParse\ImporteCentavos;
use Tests\TestCase;

class ImporteCentavosTest extends TestCase
{
    public function test_convierte_los_formatos_aceptados(): void
    {
        $casos = [
            '4,748,413.33' => 474841333,
            '4748413.33' => 474841333,
            '4.748.413,33' => 474841333,
            '4,748,413' => 474841300,
            '1.000' => 100000,
            '1,000' => 100000,
            '1000' => 100000,
            '1,23' => 123,
            '1.23' => 123,
            '0,00' => 0,
            '-1.234,56' => -123456,
            '32666328.931' => 3266632893,
            '$ 558.4OO,44' => 55840044,
        ];

        foreach ($casos as $texto => $esperado) {
            $resultado = ImporteCentavos::desdeTexto($texto);

            $this->assertTrue($resultado['valido'], 'No valido: '.$texto);
            $this->assertSame($esperado, $resultado['centavos'], 'Importe incorrecto: '.$texto);
        }
    }

    public function test_rechaza_textos_sin_digitos(): void
    {
        foreach ([null, '', '   ', 'abc', '.', ','] as $texto) {
            $resultado = ImporteCentavos::desdeTexto($texto);

            $this->assertFalse($resultado['valido']);
            $this->assertNull($resultado['centavos']);
        }
    }

    public function test_registra_correcciones_ocr(): void
    {
        $resultado = ImporteCentavos::desdeTexto('1O,5O');

        $this->assertSame(1050, $resultado['centavos']);
        $this->assertCount(2, $resultado['correcciones']);
        $this->assertSame('O', $resultado['correcciones'][0]['original']);
        $this->assertSame('0', $resultado['correcciones'][0]['corregido']);
        $this->assertSame('1O,5O', $resultado['original']);
    }

    public function test_formatea_centavos_sin_float(): void
    {
        $this->assertSame('$ 4.748.413,33', ImporteCentavos::formatear(474841333));
        $this->assertSame('-$ 1.234,56', ImporteCentavos::formatear(-123456));
        $this->assertSame('0,05', ImporteCentavos::formatear(5, false));
    }

    public function test_a_centavos_acepta_enteros_internos(): void
    {
        $this->assertSame(474841333, ImporteCentavos::aCentavos(474841333));
        $this->assertSame(123456, ImporteCentavos::aCentavos('1.234,56'));
        $this->assertNull(ImporteCentavos::aCentavos(null));
    }
}
