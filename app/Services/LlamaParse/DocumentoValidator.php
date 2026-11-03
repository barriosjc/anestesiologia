<?php

namespace App\Services\LlamaParse;

use Illuminate\Support\Facades\Validator;

class DocumentoValidator
{
    /**
     * Correcciones de OCR para campos que deben ser numericos.
     *
     * @var array<string, string>
     */
    private const OCR_DIGITOS = [
        'O' => '0', 'o' => '0', 'Q' => '0', 'D' => '0',
        'I' => '1', 'l' => '1', 'i' => '1', '|' => '1',
        'Z' => '2', 'z' => '2',
        'S' => '5', 's' => '5',
        'G' => '6', 'b' => '6',
        'T' => '7',
        'B' => '8',
        'g' => '9', 'q' => '9',
    ];

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function validar(
        array $datos,
        bool $consolidarAtenciones = true,
        bool $aplicarCorreccion17 = true,
        bool $importesEnCentavos = false
    ): array {
        $datos = $this->normalizar($datos);

        $validador = Validator::make($datos, [
            'documento' => ['nullable', 'array'],
            'documento.tipo' => ['nullable', 'string', 'in:orden_pago,detalle_liquidacion,parte,consumo,presupuesto,otro'],
            'documento.numero' => ['nullable', 'string'],
            'documento.numero_original' => ['nullable', 'string'],
            'documento.numero_revisar' => ['nullable', 'boolean'],
            'documento.fecha' => ['nullable', 'string'],
            'documento.paginas' => ['nullable', 'integer', 'min:0'],
            'documento.paginas_reales' => ['nullable', 'integer', 'min:0'],
            'documento.paginas_procesadas' => ['nullable', 'integer', 'min:0'],
            'documento.total_informado' => ['nullable', 'numeric'],
            'documento.total_informado_original' => ['nullable', 'string'],
            'documento.total_informado_revisar' => ['nullable', 'boolean'],
            'documento.hash_sha256' => ['nullable', 'string', 'max:64'],
            'documento.paginas_pdf' => ['nullable', 'integer', 'min:1'],
            'documento.total_liquidacion_centavos' => ['nullable', 'integer'],
            'documento.total_orden_pago_centavos' => ['nullable', 'integer'],

            'cabecera' => ['nullable', 'array'],
            'cabecera.profesional_codigo' => ['nullable', 'string'],
            'cabecera.profesional' => ['nullable', 'string'],
            'cabecera.concepto' => ['nullable', 'string'],
            'cabecera.importe' => ['nullable', 'numeric'],
            'cabecera.importe_original' => ['nullable', 'string'],
            'cabecera.importe_revisar' => ['nullable', 'boolean'],
            'cabecera.cuit' => ['nullable', 'string'],
            'cabecera.cuit_valido' => ['nullable', 'boolean'],
            'cabecera.condicion_iva' => ['nullable', 'string'],
            'cabecera.comprobante' => ['nullable', 'string'],
            'cabecera.numero_liquidacion' => ['nullable', 'string'],
            'cabecera.retencion_caja_centavos' => ['nullable', 'integer'],
            'cabecera.retencion_gastos_11_centavos' => ['nullable', 'integer'],
            'cabecera.retencion_gastos_5_centavos' => ['nullable', 'integer'],
            'cabecera.total_retenciones_centavos' => ['nullable', 'integer'],
            'cabecera.transferencia_centavos' => ['nullable', 'integer'],
            'cabecera.alicuota_caja' => ['nullable', 'numeric'],
            'cabecera.alicuota_gastos_11' => ['nullable', 'numeric'],
            'cabecera.alicuota_gastos_5' => ['nullable', 'numeric'],

            'atenciones' => ['nullable', 'array'],
            'atenciones.*' => ['array'],
            'atenciones.*.obra_social' => ['nullable', 'string'],
            'atenciones.*.plan' => ['nullable', 'string'],
            'atenciones.*.atencion' => ['nullable', 'string'],
            'atenciones.*.atencion_original' => ['nullable', 'string'],
            'atenciones.*.atencion_revisar' => ['nullable', 'boolean'],
            'atenciones.*.fecha' => ['nullable', 'string'],
            'atenciones.*.numero_afiliado' => ['nullable', 'string'],
            'atenciones.*.numero_afiliado_original' => ['nullable', 'string'],
            'atenciones.*.numero_afiliado_revisar' => ['nullable', 'boolean'],
            'atenciones.*.nombre_afiliado' => ['nullable', 'string'],
            'atenciones.*.total_atencion' => ['nullable', 'numeric'],
            'atenciones.*.total_atencion_original' => ['nullable', 'string'],
            'atenciones.*.total_atencion_revisar' => ['nullable', 'boolean'],
            'atenciones.*.pagina_inicio' => ['nullable', 'integer', 'min:1'],
            'atenciones.*.pagina_fin' => ['nullable', 'integer', 'min:1'],
            'atenciones.*.texto_crudo' => ['nullable', 'string'],
            'atenciones.*.valido' => ['nullable', 'boolean'],
            'atenciones.*.errores' => ['nullable', 'array'],
            'atenciones.*.errores.*' => ['string'],
            'atenciones.*.indice_bloque' => ['nullable', 'integer', 'min:0'],
            'atenciones.*.clave_bloque' => ['nullable', 'string'],
            'atenciones.*.detalles' => ['nullable', 'array'],
            'atenciones.*.detalles.*' => ['array'],
            'atenciones.*.detalles.*.fecha' => ['nullable', 'string'],
            'atenciones.*.detalles.*.codigo_nivel' => ['nullable', 'string'],
            'atenciones.*.detalles.*.codigo_nivel_original' => ['nullable', 'string'],
            'atenciones.*.detalles.*.codigo_nivel_revisar' => ['nullable', 'boolean'],
            'atenciones.*.detalles.*.descripcion_nivel' => ['nullable', 'string'],
            'atenciones.*.detalles.*.periodo' => ['nullable', 'string'],
            'atenciones.*.detalles.*.porcentaje_cobertura' => ['nullable', 'numeric'],
            'atenciones.*.detalles.*.porcentaje_cobertura_original' => ['nullable', 'string'],
            'atenciones.*.detalles.*.porcentaje_cobertura_revisar' => ['nullable', 'boolean'],
            'atenciones.*.detalles.*.cantidad' => ['nullable', 'numeric'],
            'atenciones.*.detalles.*.cantidad_original' => ['nullable', 'string'],
            'atenciones.*.detalles.*.cantidad_revisar' => ['nullable', 'boolean'],
            'atenciones.*.detalles.*.importe' => ['nullable', 'numeric'],
            'atenciones.*.detalles.*.importe_original' => ['nullable', 'string'],
            'atenciones.*.detalles.*.importe_revisar' => ['nullable', 'boolean'],
            'atenciones.*.detalles.*.pagina' => ['nullable', 'integer', 'min:0'],
            'atenciones.*.detalles.*.neto_centavos' => ['nullable', 'integer'],
            'atenciones.*.detalles.*.iva_centavos' => ['nullable', 'integer'],
            'atenciones.*.detalles.*.texto_crudo' => ['nullable', 'string'],
            'atenciones.*.detalles.*.origen_dato' => ['nullable', 'string', 'in:regla,modelo'],
            'atenciones.*.detalles.*.valido' => ['nullable', 'boolean'],
            'atenciones.*.detalles.*.motivo' => ['nullable', 'string'],
            'atenciones.*.detalles.*.indice_linea' => ['nullable', 'integer', 'min:0'],

            'validacion' => ['nullable', 'array'],
            'confianza' => ['nullable', 'numeric'],
            'observaciones' => ['nullable', 'string'],
            'texto_crudo' => ['nullable', 'string'],
        ]);

        $errores = [];
        if ($validador->fails()) {
            $errores = $validador->errors()->all();
        }

        $resultado = $this->normalizarResultado(
            $datos,
            $consolidarAtenciones,
            $aplicarCorreccion17,
            $importesEnCentavos
        );
        $resultado['advertencias'] = array_values(array_unique(array_merge(
            $errores,
            $resultado['advertencias'] ?? []
        )));

        return $resultado;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function normalizar(array $datos): array
    {
        $datos['documento'] = is_array($datos['documento'] ?? null) ? $datos['documento'] : [];
        $datos['cabecera'] = is_array($datos['cabecera'] ?? null) ? $datos['cabecera'] : [];
        $datos['atenciones'] = is_array($datos['atenciones'] ?? null) ? $datos['atenciones'] : [];

        if (isset($datos['confianza']) && is_numeric($datos['confianza'])) {
            $confianza = (float) $datos['confianza'];

            if ($confianza > 1 && $confianza <= 100) {
                $datos['confianza'] = $confianza / 100;
            }
        }

        if (array_key_exists('importe', $datos['cabecera'])) {
            $datos['cabecera']['importe'] = $this->aNumero($datos['cabecera']['importe']);
        }

        if (array_key_exists('total_informado', $datos['documento'])) {
            $datos['documento']['total_informado'] = $this->aNumero($datos['documento']['total_informado']);
        }

        foreach (['paginas_pdf'] as $campo) {
            if (isset($datos['documento'][$campo]) && $datos['documento'][$campo] !== '') {
                $datos['documento'][$campo] = (int) $datos['documento'][$campo];
            }
        }

        foreach (['total_liquidacion_centavos', 'total_orden_pago_centavos'] as $campo) {
            if (array_key_exists($campo, $datos['documento'])) {
                $datos['documento'][$campo] = $this->aEntero($datos['documento'][$campo]);
            }
        }

        foreach ([
            'retencion_caja_centavos',
            'retencion_gastos_11_centavos',
            'retencion_gastos_5_centavos',
            'total_retenciones_centavos',
            'transferencia_centavos',
        ] as $campo) {
            if (array_key_exists($campo, $datos['cabecera'])) {
                $datos['cabecera'][$campo] = $this->aEntero($datos['cabecera'][$campo]);
            }
        }

        foreach (['alicuota_caja', 'alicuota_gastos_11', 'alicuota_gastos_5'] as $campo) {
            if (array_key_exists($campo, $datos['cabecera'])) {
                $datos['cabecera'][$campo] = $this->aNumero($datos['cabecera'][$campo]);
            }
        }

        $datos['atenciones'] = array_values(array_map(function ($atencion) {
            if (! is_array($atencion)) {
                return [];
            }

            foreach (['pagina_inicio', 'pagina_fin', 'indice_bloque'] as $campo) {
                if (isset($atencion[$campo]) && $atencion[$campo] !== '') {
                    $atencion[$campo] = (int) $atencion[$campo];
                }
            }

            $atencion['detalles'] = array_map(function ($detalle) {
                if (! is_array($detalle)) {
                    return [];
                }

                foreach (['porcentaje_cobertura', 'cantidad', 'importe'] as $campo) {
                    if (array_key_exists($campo, $detalle)) {
                        $detalle[$campo] = $this->aNumero($detalle[$campo]);
                    }
                }

                foreach (['neto_centavos', 'iva_centavos'] as $campo) {
                    if (array_key_exists($campo, $detalle)) {
                        $detalle[$campo] = $this->aEntero($detalle[$campo]);
                    }
                }

                if (isset($detalle['indice_linea']) && $detalle['indice_linea'] !== '') {
                    $detalle['indice_linea'] = (int) $detalle['indice_linea'];
                }

                return $detalle;
            }, is_array($atencion['detalles'] ?? null) ? $atencion['detalles'] : []);

            return $atencion;
        }, $datos['atenciones']));

        return $datos;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function normalizarResultado(
        array $datos,
        bool $consolidarAtenciones = true,
        bool $aplicarCorreccion17 = true,
        bool $importesEnCentavos = false
    ): array {
        $texto = fn ($valor) => $this->aTexto($valor);
        $advertencias = is_array($datos['advertencias'] ?? null) ? $datos['advertencias'] : [];

        $numeroDocumento = $this->corregirNumeroOcr($datos['documento']['numero'] ?? null);
        $numeroDocumentoOriginal = $texto($datos['documento']['numero_original'] ?? null)
            ?? $texto($datos['documento']['numero'] ?? null);

        if ($numeroDocumento['revisar']) {
            $advertencias[] = 'Numero de documento con caracteres dudosos: '.($numeroDocumentoOriginal ?? 'sin dato');
        }

        if (! empty($numeroDocumento['descartados'])) {
            $advertencias[] = 'Numero de documento: se descartaron caracteres no numericos ('.implode('', $numeroDocumento['descartados']).').';
        }

        $paginasReales = isset($datos['documento']['paginas_reales'])
            ? (int) $datos['documento']['paginas_reales']
            : null;
        $paginasProcesadas = isset($datos['documento']['paginas_procesadas'])
            ? (int) $datos['documento']['paginas_procesadas']
            : null;

        if ($paginasReales !== null && $paginasProcesadas !== null && $paginasProcesadas < $paginasReales) {
            $advertencias[] = "Se procesaron {$paginasProcesadas} de {$paginasReales} paginas reales.";
        }

        $importeCabecera = $this->aNumero($datos['cabecera']['importe'] ?? null);
        $importeCabeceraOriginal = $texto($datos['cabecera']['importe_original'] ?? null)
            ?? $texto($datos['cabecera']['importe'] ?? null);

        if ($this->aBool($datos['cabecera']['importe_revisar'] ?? false)) {
            $advertencias[] = 'Importe de cabecera con caracteres dudosos: '.($importeCabeceraOriginal ?? 'sin dato');
        }

        $atenciones = array_values(array_map(function ($atencion, $indice) use ($texto, $aplicarCorreccion17, $importesEnCentavos) {
            $detalles = is_array($atencion['detalles'] ?? null) ? $atencion['detalles'] : [];
            $advertenciasAtencion = [];
            $atencionCorregida = $aplicarCorreccion17
                ? $this->corregirAtencion17($atencion['atencion'] ?? null)
                : $this->corregirAtencionSin17($atencion['atencion'] ?? null);
            $afiliadoCorregido = $this->corregirNumeroOcr($atencion['numero_afiliado'] ?? null);
            $atencionOriginal = $texto($atencion['atencion_original'] ?? null)
                ?? $texto($atencion['atencion'] ?? null);
            $afiliadoOriginal = $texto($atencion['numero_afiliado_original'] ?? null)
                ?? $texto($atencion['numero_afiliado'] ?? null);

            if ($atencionCorregida['revisar']) {
                $advertenciasAtencion[] = 'Nro de atencion con caracteres dudosos: '.($atencionOriginal ?? 'sin dato');
            }

            if (! empty($atencionCorregida['descartados'])) {
                $advertenciasAtencion[] = 'Nro de atencion: se descartaron caracteres ('.implode('', $atencionCorregida['descartados']).').';
            }

            if ($afiliadoCorregido['revisar']) {
                $advertenciasAtencion[] = 'Nro de afiliado con caracteres dudosos: '.($afiliadoOriginal ?? 'sin dato');
            }

            if (! empty($afiliadoCorregido['descartados'])) {
                $advertenciasAtencion[] = 'Nro de afiliado: se descartaron caracteres ('.implode('', $afiliadoCorregido['descartados']).').';
            }

            if (! empty($atencionCorregida['corregido_17'])) {
                $advertenciasAtencion[] = 'Nro de atencion corregido de 17... a 11...: '
                    .($atencionOriginal ?? 'sin dato').' -> '.$atencionCorregida['valor'];
            }

            if ($atencionCorregida['valor'] !== null
                && strlen($atencionCorregida['valor']) !== 7) {
                $advertenciasAtencion[] = 'Nro de atencion fuera del formato esperado (7 digitos): '
                    .$atencionCorregida['valor'];
            }

            $nombreAfiliado = $texto($atencion['nombre_afiliado'] ?? null);

            if ($nombreAfiliado === null) {
                $advertenciasAtencion[] = 'Nombre de afiliado vacio.';
            } elseif (preg_match('/,\s*$/', $nombreAfiliado) === 1) {
                $advertenciasAtencion[] = 'Nombre de afiliado posiblemente truncado (termina en coma).';
            }

            $totalCalculado = array_sum(array_map(
                fn ($detalle) => is_array($detalle)
                    ? ($importesEnCentavos
                        ? (int) ($this->aNumero($detalle['importe'] ?? null) ?? 0)
                        : (float) ($this->aNumero($detalle['importe'] ?? null) ?? 0))
                    : 0,
                $detalles
            ));
            $totalInformado = $this->aNumero($atencion['total_atencion'] ?? null);
            $totalOriginal = $texto($atencion['total_atencion_original'] ?? null)
                ?? $texto($atencion['total_atencion'] ?? null);

            $totalNoCoincide = $totalInformado !== null
                && ($importesEnCentavos
                    ? (int) $totalInformado !== (int) $totalCalculado
                    : abs($totalInformado - $totalCalculado) > 0.01);

            if ($totalNoCoincide) {
                $advertenciasAtencion[] = 'Total de atencion no coincide: informado '
                    .$totalInformado.', calculado '.$totalCalculado.'.';
            }

            $salidaAtencion = [
                'bloque_id' => $indice + 1,
                'orden' => $indice + 1,
                'obra_social' => $texto($atencion['obra_social'] ?? null),
                'plan' => $texto($atencion['plan'] ?? null),
                'atencion' => $atencionCorregida['valor'],
                'atencion_original' => $atencionOriginal,
                'atencion_revisar' => $atencionCorregida['revisar']
                    || $this->aBool($atencion['atencion_revisar'] ?? false),
                'fecha' => $texto($atencion['fecha'] ?? null),
                'numero_afiliado' => $afiliadoCorregido['valor'],
                'numero_afiliado_original' => $afiliadoOriginal,
                'numero_afiliado_revisar' => $afiliadoCorregido['revisar']
                    || $this->aBool($atencion['numero_afiliado_revisar'] ?? false),
                'nombre_afiliado' => $nombreAfiliado,
                'total_atencion' => $totalInformado,
                'total_atencion_original' => $totalOriginal,
                'total_atencion_revisar' => $this->aBool($atencion['total_atencion_revisar'] ?? false),
                'total_calculado' => $totalCalculado,
                'advertencias' => array_values(array_unique($advertenciasAtencion)),
                'detalles' => array_values(array_map(function ($detalle) use ($texto) {
                    $advertenciasDetalle = [];
                    $porcentaje = $this->aNumero($detalle['porcentaje_cobertura'] ?? null);
                    $cantidad = $this->aNumero($detalle['cantidad'] ?? null);
                    $importe = $this->aNumero($detalle['importe'] ?? null);
                    $porcentajeOriginal = $texto($detalle['porcentaje_cobertura_original'] ?? null)
                        ?? $texto($detalle['porcentaje_cobertura'] ?? null);
                    $cantidadOriginal = $texto($detalle['cantidad_original'] ?? null)
                        ?? $texto($detalle['cantidad'] ?? null);
                    $importeOriginal = $texto($detalle['importe_original'] ?? null)
                        ?? $texto($detalle['importe'] ?? null);

                    if ($this->aBool($detalle['porcentaje_cobertura_revisar'] ?? false)
                        || ($porcentaje === null && $porcentajeOriginal !== null)) {
                        $advertenciasDetalle[] = 'Porcentaje de cobertura dudoso: '.($porcentajeOriginal ?? 'sin dato');
                    }

                    if ($this->aBool($detalle['cantidad_revisar'] ?? false)
                        || ($cantidad === null && $cantidadOriginal !== null)) {
                        $advertenciasDetalle[] = 'Cantidad dudosa: '.($cantidadOriginal ?? 'sin dato');
                    }

                    if ($this->aBool($detalle['importe_revisar'] ?? false)
                        || ($importe === null && $importeOriginal !== null)) {
                        $advertenciasDetalle[] = 'Importe dudoso: '.($importeOriginal ?? 'sin dato');
                    }

                    if ($this->aBool($detalle['codigo_nivel_revisar'] ?? false)) {
                        $advertenciasDetalle[] = 'Codigo de nivel dudoso: '.($texto($detalle['codigo_nivel_original'] ?? null) ?? 'sin dato');
                    }

                    $salidaDetalle = [
                        'fecha' => $texto($detalle['fecha'] ?? null),
                        'codigo_nivel' => $texto($detalle['codigo_nivel'] ?? null),
                        'codigo_nivel_original' => $texto($detalle['codigo_nivel_original'] ?? null)
                            ?? $texto($detalle['codigo_nivel'] ?? null),
                        'codigo_nivel_revisar' => $this->aBool($detalle['codigo_nivel_revisar'] ?? false),
                        'descripcion_nivel' => $texto($detalle['descripcion_nivel'] ?? null),
                        'periodo' => $texto($detalle['periodo'] ?? null),
                        'porcentaje_cobertura' => $porcentaje,
                        'porcentaje_cobertura_original' => $porcentajeOriginal,
                        'porcentaje_cobertura_revisar' => $this->aBool($detalle['porcentaje_cobertura_revisar'] ?? false)
                            || ($porcentaje === null && $porcentajeOriginal !== null),
                        'cantidad' => $cantidad,
                        'cantidad_original' => $cantidadOriginal,
                        'cantidad_revisar' => $this->aBool($detalle['cantidad_revisar'] ?? false)
                            || ($cantidad === null && $cantidadOriginal !== null),
                        'importe' => $importe,
                        'importe_original' => $importeOriginal,
                        'importe_revisar' => $this->aBool($detalle['importe_revisar'] ?? false)
                            || ($importe === null && $importeOriginal !== null),
                        'pagina' => isset($detalle['pagina']) ? (int) $detalle['pagina'] : null,
                        'advertencias' => array_values(array_unique($advertenciasDetalle)),
                    ];

                    return array_merge($salidaDetalle, $this->preservar($detalle, [
                        'neto_centavos',
                        'iva_centavos',
                        'texto_crudo',
                        'origen_dato',
                        'valido',
                        'motivo',
                        'indice_linea',
                    ]));
                }, $detalles)),
            ];

            return array_merge($salidaAtencion, $this->preservar($atencion, [
                'pagina_inicio',
                'pagina_fin',
                'texto_crudo',
                'valido',
                'errores',
                'indice_bloque',
                'clave_bloque',
            ]));
        }, $datos['atenciones'], array_keys($datos['atenciones'])));

        $atenciones = $consolidarAtenciones
            ? $this->consolidarAtenciones($atenciones)
            : $this->marcarBloques($atenciones);
        $resumen = $this->resumen($atenciones);

        $totalInformado = $this->aNumero($datos['documento']['total_informado'] ?? null);
        $totalOriginal = $texto($datos['documento']['total_informado_original'] ?? null)
            ?? $texto($datos['documento']['total_informado'] ?? null);
        $totalCalculado = array_sum(array_map(
            fn ($atencion) => array_sum(array_map(
                fn ($detalle) => $importesEnCentavos
                    ? (int) ($detalle['importe'] ?? 0)
                    : (float) ($detalle['importe'] ?? 0),
                $atencion['detalles'] ?? []
            )),
            $atenciones
        ));
        $totalOk = $totalInformado === null
            ? null
            : ($importesEnCentavos
                ? (int) $totalInformado === (int) $totalCalculado
                : abs($totalInformado - $totalCalculado) <= 0.01);

        $resumen['total_informado'] = $totalInformado;
        $resumen['total_calculado'] = $totalCalculado;
        $resumen['total_ok'] = $totalOk;

        if ($totalOk === false) {
            $advertencias[] = "El total calculado {$totalCalculado} no coincide con el total informado {$totalInformado}.";
        }

        if ($this->aBool($datos['documento']['total_informado_revisar'] ?? false)) {
            $advertencias[] = 'Total informado con caracteres dudosos: '.($totalOriginal ?? 'sin dato');
        }

        $esperadas = config('llamaparse.atenciones_esperadas');

        if (is_int($esperadas) && $esperadas > 0 && $resumen['atenciones_unicas'] < $esperadas) {
            $advertencias[] = "Se detectaron {$resumen['atenciones_unicas']} atenciones unicas; "
                ."se esperaban {$esperadas}. Revise el archivo y corrija manualmente.";
        }

        $confianza = isset($datos['confianza']) && is_numeric($datos['confianza'])
            ? (float) $datos['confianza']
            : null;

        if ($confianza !== null && ($confianza < 0 || $confianza > 1)) {
            $advertencias[] = 'Confianza fuera de rango (0-1): '.$confianza;
        }

        $documentoSalida = array_merge([
            'tipo' => $texto($datos['documento']['tipo'] ?? null),
            'numero' => $numeroDocumento['valor'],
            'numero_original' => $numeroDocumentoOriginal,
            'numero_revisar' => $numeroDocumento['revisar']
                || $this->aBool($datos['documento']['numero_revisar'] ?? false),
            'fecha' => $texto($datos['documento']['fecha'] ?? null),
            'paginas' => isset($datos['documento']['paginas']) ? (int) $datos['documento']['paginas'] : null,
            'paginas_reales' => $paginasReales,
            'paginas_procesadas' => $paginasProcesadas,
            'total_informado' => $totalInformado,
            'total_informado_original' => $totalOriginal,
            'total_informado_revisar' => $this->aBool($datos['documento']['total_informado_revisar'] ?? false)
                || ($totalInformado === null && $totalOriginal !== null),
            'total_calculado' => $totalCalculado,
        ], $this->preservar($datos['documento'], [
            'hash_sha256',
            'paginas_pdf',
            'total_liquidacion_centavos',
            'total_orden_pago_centavos',
        ]));

        $cabeceraSalida = array_merge([
            'profesional_codigo' => $texto($datos['cabecera']['profesional_codigo'] ?? null),
            'profesional' => $texto($datos['cabecera']['profesional'] ?? null),
            'concepto' => $texto($datos['cabecera']['concepto'] ?? null),
            'importe' => $importeCabecera,
            'importe_original' => $importeCabeceraOriginal,
            'importe_revisar' => $this->aBool($datos['cabecera']['importe_revisar'] ?? false)
                || ($importeCabecera === null && $importeCabeceraOriginal !== null),
        ], $this->preservar($datos['cabecera'], [
            'cuit',
            'cuit_valido',
            'condicion_iva',
            'comprobante',
            'numero_liquidacion',
            'retencion_caja_centavos',
            'retencion_gastos_11_centavos',
            'retencion_gastos_5_centavos',
            'total_retenciones_centavos',
            'transferencia_centavos',
            'alicuota_caja',
            'alicuota_gastos_11',
            'alicuota_gastos_5',
        ]));

        $salida = [
            'documento' => $documentoSalida,
            'cabecera' => $cabeceraSalida,
            'atenciones' => $atenciones,
            'resumen' => $resumen,
            'confianza' => $confianza,
            'observaciones' => $texto($datos['observaciones'] ?? null),
            'texto_crudo' => $texto($datos['texto_crudo'] ?? null),
            'advertencias' => array_values(array_unique($advertencias)),
        ];

        if (array_key_exists('validacion', $datos)) {
            $salida['validacion'] = is_array($datos['validacion']) ? $datos['validacion'] : null;
        }

        return $salida;
    }

    /**
     * Consolida detalles de bloques que son claramente la misma atencion:
     * mismo numero + fecha + paciente + obra social. No usa solo el numero,
     * porque el mismo numero puede repetirse para otro paciente u otro bloque.
     *
     * @param  array<int, array<string, mixed>>  $atenciones
     * @return array<int, array<string, mixed>>
     */
    private function consolidarAtenciones(array $atenciones): array
    {
        $resultado = [];
        $indices = [];

        foreach ($atenciones as $atencion) {
            $clave = $this->claveAtencion($atencion);

            if ($clave === null || ! array_key_exists($clave, $indices)) {
                if ($clave !== null) {
                    $indices[$clave] = count($resultado);
                }

                $resultado[] = $atencion;

                continue;
            }

            $indice = $indices[$clave];
            $resultado[$indice]['detalles'] = array_merge(
                $resultado[$indice]['detalles'] ?? [],
                $atencion['detalles'] ?? []
            );

            $totalPrevio = $resultado[$indice]['total_atencion'] ?? null;
            $totalNuevo = $atencion['total_atencion'] ?? null;

            if ($totalPrevio !== null && $totalNuevo !== null) {
                $resultado[$indice]['total_atencion'] = (float) $totalPrevio + (float) $totalNuevo;
            }

            foreach ([
                'obra_social', 'plan', 'fecha', 'numero_afiliado',
                'numero_afiliado_original', 'nombre_afiliado',
                'total_atencion', 'total_atencion_original',
            ] as $campo) {
                if (($resultado[$indice][$campo] ?? null) === null && ($atencion[$campo] ?? null) !== null) {
                    $resultado[$indice][$campo] = $atencion[$campo];
                }
            }

            $resultado[$indice]['advertencias'] = array_values(array_unique(array_merge(
                $resultado[$indice]['advertencias'] ?? [],
                $atencion['advertencias'] ?? []
            )));

            if ($atencion['atencion_revisar'] ?? false) {
                $resultado[$indice]['atencion_revisar'] = true;
            }

            if ($atencion['numero_afiliado_revisar'] ?? false) {
                $resultado[$indice]['numero_afiliado_revisar'] = true;
            }

            $resultado[$indice]['total_calculado'] = array_sum(array_map(
                fn ($detalle) => (float) ($detalle['importe'] ?? 0),
                $resultado[$indice]['detalles']
            ));

            $informado = $resultado[$indice]['total_atencion'] ?? null;
            $calculado = $resultado[$indice]['total_calculado'];

            if ($informado !== null && abs((float) $informado - (float) $calculado) > 0.01) {
                $resultado[$indice]['advertencias'][] = 'Total de atencion no coincide: informado '
                    .$informado.', calculado '.$calculado.'.';
                $resultado[$indice]['advertencias'] = array_values(array_unique($resultado[$indice]['advertencias']));
            }
        }

        foreach ($resultado as $i => &$atencion) {
            $atencion['bloque_id'] = $i + 1;
            $atencion['orden'] = $i + 1;
        }
        unset($atencion);

        return array_values($resultado);
    }

    /**
     * Asigna bloque_id/orden sin consolidar ni deduplicar.
     *
     * @param  array<int, array<string, mixed>>  $atenciones
     * @return array<int, array<string, mixed>>
     */
    private function marcarBloques(array $atenciones): array
    {
        foreach ($atenciones as $i => &$atencion) {
            $atencion['bloque_id'] = $i + 1;
            $atencion['orden'] = $i + 1;
        }
        unset($atencion);

        return array_values($atenciones);
    }

    /**
     * @param  array<string, mixed>  $origen
     * @param  array<int, string>  $claves
     * @return array<string, mixed>
     */
    private function preservar(array $origen, array $claves): array
    {
        $resultado = [];

        foreach ($claves as $clave) {
            if (array_key_exists($clave, $origen)) {
                $resultado[$clave] = $origen[$clave];
            }
        }

        return $resultado;
    }

    /**
     * @param  array<string, mixed>  $atencion
     */
    private function claveAtencion(array $atencion): ?string
    {
        $numero = $atencion['atencion'] ?? null;

        if ($numero === null || $numero === '') {
            return null;
        }

        $fecha = $this->normalizarClave($atencion['fecha'] ?? null);
        $nombre = $this->normalizarClave($atencion['nombre_afiliado'] ?? null);
        $obra = $this->normalizarClave($atencion['obra_social'] ?? null);

        if ($fecha === '' && $nombre === '' && $obra === '') {
            return null;
        }

        return implode('|', [(string) $numero, $fecha, $nombre, $obra]);
    }

    private function normalizarClave(mixed $valor): string
    {
        $texto = $this->aTexto($valor);

        if ($texto === null) {
            return '';
        }

        return mb_strtolower(preg_replace('/\s+/', ' ', $texto) ?? $texto);
    }

    /**
     * @param  array<int, array<string, mixed>>  $atenciones
     * @return array{bloques: int, atenciones_unicas: int, detalles: int}
     */
    private function resumen(array $atenciones): array
    {
        $unicas = [];
        $detalles = 0;

        foreach ($atenciones as $atencion) {
            $numero = $atencion['atencion'] ?? null;

            if ($numero !== null && $numero !== '') {
                $unicas[(string) $numero] = true;
            }

            $detalles += count($atencion['detalles'] ?? []);
        }

        return [
            'bloques' => count($atenciones),
            'atenciones_unicas' => count($unicas),
            'detalles' => $detalles,
        ];
    }

    /**
     * Corrige el error tipico de OCR 17xxxxx -> 11xxxxx en atenciones de 7 digitos.
     *
     * @return array{valor: string|null, revisar: bool, descartados: array<int, string>, corregido_17: bool}
     */
    private function corregirAtencion17(mixed $valor): array
    {
        $resultado = $this->corregirNumeroOcr($valor);
        $resultado['corregido_17'] = false;

        if (config('llamaparse.corregir_17_a_11', true)
            && $resultado['valor'] !== null
            && preg_match('/^17\d{5}$/', $resultado['valor']) === 1) {
            $resultado['valor'] = '11'.substr($resultado['valor'], 2);
            $resultado['revisar'] = true;
            $resultado['corregido_17'] = true;
        }

        return $resultado;
    }

    /**
     * @return array{valor: string|null, revisar: bool, descartados: array<int, string>, corregido_17: bool}
     */
    private function corregirAtencionSin17(mixed $valor): array
    {
        $resultado = $this->corregirNumeroOcr($valor);
        $resultado['corregido_17'] = false;

        return $resultado;
    }

    /**
     * @return array{valor: string|null, revisar: bool, descartados: array<int, string>}
     */
    private function corregirNumeroOcr(mixed $valor): array
    {
        $original = $this->aTexto($valor);

        if ($original === null) {
            return ['valor' => null, 'revisar' => false, 'descartados' => []];
        }

        $corregido = '';
        $revisar = false;
        $descartados = [];
        $caracteres = preg_split('//u', $original, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($caracteres as $caracter) {
            if (preg_match('/^[0-9]$/', $caracter) === 1) {
                $corregido .= $caracter;

                continue;
            }

            if (isset(self::OCR_DIGITOS[$caracter])) {
                $corregido .= self::OCR_DIGITOS[$caracter];

                continue;
            }

            if (in_array($caracter, [' ', '.', '-', '/', ','], true)) {
                continue;
            }

            $revisar = true;
            $descartados[] = $caracter;
        }

        return [
            'valor' => $corregido === '' ? null : $corregido,
            'revisar' => $revisar,
            'descartados' => $descartados,
        ];
    }

    private function aBool(mixed $valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }

        if (is_string($valor)) {
            return in_array(strtolower(trim($valor)), ['1', 'true', 'si', 'sí'], true);
        }

        return (bool) $valor;
    }

    private function aTexto(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_bool($valor)) {
            return $valor ? '1' : '0';
        }

        if (is_scalar($valor)) {
            return trim((string) $valor);
        }

        return null;
    }

    private function aNumero(mixed $valor): int|float|null
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_int($valor) || is_float($valor)) {
            return $valor;
        }

        $texto = strtr((string) $valor, self::OCR_DIGITOS);
        $texto = preg_replace('/[^\d,.\-]/', '', $texto);
        $texto = preg_replace('/\.{2,}/', '.', (string) $texto);

        if ($texto === '' || $texto === null) {
            return null;
        }

        $tieneComa = str_contains($texto, ',');
        $tienePunto = str_contains($texto, '.');

        if ($tieneComa && $tienePunto) {
            $ultimaComa = strrpos($texto, ',');
            $ultimoPunto = strrpos($texto, '.');

            if ($ultimaComa !== false && $ultimoPunto !== false && $ultimaComa > $ultimoPunto) {
                // Formato europeo: 3.988.667,19
                $texto = str_replace('.', '', $texto);
                $texto = str_replace(',', '.', $texto);
            } else {
                // Formato US: 1,810,612.26
                $texto = str_replace(',', '', $texto);
            }
        } elseif ($tieneComa) {
            $partes = explode(',', $texto);

            if (count($partes) > 2 || (count($partes) === 2 && strlen($partes[1]) === 3)) {
                // 1,234,567 o 38,517 -> miles
                $texto = str_replace(',', '', $texto);
            } else {
                // 38,5 -> decimal
                $texto = str_replace(',', '.', $texto);
            }
        } elseif ($tienePunto) {
            $partes = explode('.', $texto);

            if (count($partes) > 2) {
                // 1.234.567 -> miles
                $texto = str_replace('.', '', $texto);
            }
            // Un solo punto se deja como decimal: 4748413.33 / 32666328.931
        }

        return is_numeric($texto) ? (float) $texto : null;
    }

    private function aEntero(mixed $valor): ?int
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

        $texto = trim((string) $valor);
        $texto = strtr($texto, self::OCR_DIGITOS);
        $texto = preg_replace('/[^\d\-]/', '', $texto) ?? '';

        if ($texto === '' || $texto === '-' || ! is_numeric($texto)) {
            return null;
        }

        return (int) $texto;
    }
}
