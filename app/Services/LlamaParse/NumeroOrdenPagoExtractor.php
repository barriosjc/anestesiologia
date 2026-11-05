<?php

namespace App\Services\LlamaParse;

/**
 * Extrae el numero de la orden de pago desde el markdown de LlamaParse.
 *
 * El XLSX de tablas que devuelve LlamaParse no incluye el titulo del
 * comprobante, por eso el numero de la OP ("OPL X 0000-00077740") solo queda
 * en el markdown. Sin esto, la auditoria caia en el fallback "DOC-<id>" y la
 * columna "Nro OP" mostraba el id del documento.
 *
 * Devuelve solo los digitos (ej: "000000077740") para que coincida con el
 * formato que normaliza DocumentoValidator en documento.numero.
 */
class NumeroOrdenPagoExtractor
{
    /**
     * Patrones ordenados de mas a menos especifico. El grupo 1 es el numero.
     *
     * @var array<int, string>
     */
    private const PATRONES = [
        // OPL X 0000-00077740 / OPL 0000-00077740 / OPL0000-00077740
        '/\bO[P0][Ll](?![a-z])[^0-9\r\n]{0,15}([0-9]{1,6}[ ]*-[ ]*[0-9]{4,10}|[0-9]{5,12})/iu',
        // ORDEN DE PAGO Nº 0000-00077740
        '/\bORDEN\s+DE\s+PAGO\b[^0-9\r\n]{0,20}([0-9]{1,6}[ ]*-[ ]*[0-9]{4,10}|[0-9]{5,12})/iu',
        // OP Nº 0000-00077740
        '/\bOP\b[ ]*(?:N[º°o]?\.?)?[ ]*[:\-]?[ ]*([0-9]{1,6}[ ]*-[ ]*[0-9]{4,10}|[0-9]{5,12})/iu',
    ];

    /**
     * @param  array<int|string, mixed>|string|null  $markdown
     */
    public static function desdeMarkdown(mixed $markdown): ?string
    {
        $texto = self::aTexto($markdown);

        if ($texto === null) {
            return null;
        }

        foreach (self::PATRONES as $patron) {
            if (preg_match($patron, $texto, $coincidencia) !== 1) {
                continue;
            }

            $numero = preg_replace('/[^0-9]/', '', $coincidencia[1]) ?? '';

            if ($numero !== '') {
                return $numero;
            }
        }

        return null;
    }

    /**
     * El markdown de LlamaParse llega como texto o como lista de paginas con
     * claves anidadas; se recorren todas las cadenas.
     */
    private static function aTexto(mixed $markdown): ?string
    {
        if (is_string($markdown)) {
            return trim($markdown) === '' ? null : $markdown;
        }

        if (! is_array($markdown)) {
            return null;
        }

        $partes = [];

        array_walk_recursive($markdown, function ($valor) use (&$partes): void {
            if (is_string($valor) && trim($valor) !== '') {
                $partes[] = $valor;
            }
        });

        return $partes === [] ? null : implode("\n", $partes);
    }
}
