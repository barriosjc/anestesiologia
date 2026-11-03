<?php

namespace App\Services\LlamaParse;

final class ImporteCentavos
{
    /**
     * @var array<string, string>
     */
    private const OCR_DIGITOS = [
        'O' => '0', 'o' => '0', 'Q' => '0', 'D' => '0',
        'I' => '1', 'l' => '1', 'i' => '1', 'L' => '1', '|' => '1',
        'Z' => '2', 'z' => '2',
        'S' => '5', 's' => '5',
        'G' => '6', 'b' => '6',
        'T' => '7',
        'B' => '8',
        'g' => '9', 'q' => '9',
    ];

    /**
     * @return array{
     *     centavos: int|null,
     *     original: string|null,
     *     normalizado: string|null,
     *     correcciones: array<int, array{posicion: int, original: string, corregido: string}>,
     *     valido: bool,
     *     motivo: string|null
     * }
     */
    public static function desdeTexto(?string $texto): array
    {
        $original = $texto === null ? null : trim($texto);

        if ($original === null || $original === '') {
            return self::resultado(null, $original, null, [], false, 'Importe vacio.');
        }

        if (preg_match('/\d/', $original) !== 1) {
            return self::resultado(null, $original, null, [], false, 'No contiene digitos.');
        }

        $normalizacion = self::normalizarOcr($original);
        $textoNormalizado = $normalizacion['texto'];
        $negativo = str_starts_with($textoNormalizado, '-') || str_ends_with($textoNormalizado, '-');
        $limpio = preg_replace('/[^0-9.,]/', '', $textoNormalizado) ?? '';

        if ($limpio === '' || preg_match('/\d/', $limpio) !== 1) {
            return self::resultado(null, $original, $textoNormalizado, $normalizacion['correcciones'], false, 'No contiene digitos.');
        }

        $partes = self::separarPartes($limpio);

        if ($partes === null) {
            return self::resultado(null, $original, $textoNormalizado, $normalizacion['correcciones'], false, 'Formato de importe no reconocido.');
        }

        $entero = ltrim($partes['entero'], '0');
        $entero = $entero === '' ? '0' : $entero;
        $decimales = $partes['decimales'];

        if (strlen($decimales) > 2) {
            $resto = substr($decimales, 2);
            $decimales = substr($decimales, 0, 2);

            if ((int) $resto[0] >= 5) {
                $decimales = str_pad((string) ((int) $decimales + 1), 2, '0', STR_PAD_LEFT);

                if ((int) $decimales === 100) {
                    $entero = (string) ((int) $entero + 1);
                    $decimales = '00';
                }
            }
        }

        $decimales = str_pad($decimales, 2, '0', STR_PAD_RIGHT);
        $centavos = ((int) $entero * 100) + (int) $decimales;

        if ($negativo) {
            $centavos *= -1;
        }

        return self::resultado($centavos, $original, $textoNormalizado, $normalizacion['correcciones'], true, null);
    }

    public static function aCentavos(mixed $valor): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_int($valor)) {
            return $valor;
        }

        if (is_float($valor)) {
            return (int) round($valor);
        }

        return self::desdeTexto((string) $valor)['centavos'];
    }

    public static function formatear(int $centavos, bool $simbolo = true): string
    {
        $negativo = $centavos < 0;
        $absoluto = abs($centavos);
        $entero = (string) intdiv($absoluto, 100);
        $decimales = str_pad((string) ($absoluto % 100), 2, '0', STR_PAD_LEFT);
        $entero = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $entero) ?? $entero;

        return ($negativo ? '-' : '')
            .($simbolo ? '$ ' : '')
            .$entero
            .','
            .$decimales;
    }

    /**
     * @return array{
     *     texto: string,
     *     correcciones: array<int, array{posicion: int, original: string, corregido: string}>
     * }
     */
    public static function normalizarOcr(string $texto): array
    {
        $correcciones = [];
        $resultado = '';
        $caracteres = preg_split('//u', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($caracteres as $posicion => $caracter) {
            if (isset(self::OCR_DIGITOS[$caracter])) {
                $corregido = self::OCR_DIGITOS[$caracter];
                $resultado .= $corregido;
                $correcciones[] = [
                    'posicion' => $posicion,
                    'original' => $caracter,
                    'corregido' => $corregido,
                ];

                continue;
            }

            $resultado .= $caracter;
        }

        return [
            'texto' => $resultado,
            'correcciones' => $correcciones,
        ];
    }

    /**
     * @return array{entero: string, decimales: string}|null
     */
    private static function separarPartes(string $limpio): ?array
    {
        $ultimaComa = strrpos($limpio, ',');
        $ultimoPunto = strrpos($limpio, '.');

        if ($ultimaComa !== false && $ultimoPunto !== false) {
            if ($ultimaComa > $ultimoPunto) {
                return [
                    'entero' => str_replace([',', '.'], '', substr($limpio, 0, $ultimaComa)),
                    'decimales' => substr($limpio, $ultimaComa + 1),
                ];
            }

            return [
                'entero' => str_replace([',', '.'], '', substr($limpio, 0, $ultimoPunto)),
                'decimales' => substr($limpio, $ultimoPunto + 1),
            ];
        }

        if ($ultimaComa !== false) {
            $partes = explode(',', $limpio);

            if (count($partes) > 2
                || (count($partes) === 2 && strlen($partes[1]) === 3 && strlen($partes[0]) <= 3)) {
                return ['entero' => str_replace(',', '', $limpio), 'decimales' => ''];
            }

            return ['entero' => $partes[0], 'decimales' => $partes[1] ?? ''];
        }

        if ($ultimoPunto !== false) {
            $partes = explode('.', $limpio);

            if (count($partes) > 2
                || (count($partes) === 2 && strlen($partes[1]) === 3 && strlen($partes[0]) <= 3)) {
                return ['entero' => str_replace('.', '', $limpio), 'decimales' => ''];
            }

            return ['entero' => $partes[0], 'decimales' => $partes[1] ?? ''];
        }

        return ['entero' => $limpio, 'decimales' => ''];
    }

    /**
     * @param  array<int, array{posicion: int, original: string, corregido: string}>  $correcciones
     * @return array{
     *     centavos: int|null,
     *     original: string|null,
     *     normalizado: string|null,
     *     correcciones: array<int, array{posicion: int, original: string, corregido: string}>,
     *     valido: bool,
     *     motivo: string|null
     * }
     */
    private static function resultado(
        ?int $centavos,
        ?string $original,
        ?string $normalizado,
        array $correcciones,
        bool $valido,
        ?string $motivo
    ): array {
        return [
            'centavos' => $centavos,
            'original' => $original,
            'normalizado' => $normalizado,
            'correcciones' => $correcciones,
            'valido' => $valido,
            'motivo' => $motivo,
        ];
    }
}
