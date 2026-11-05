<?php

namespace App\Services\LlamaParse;

use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use Throwable;

class XlsxJsonService
{
    /**
     * Años (4 dígitos) encontrados en las fechas del documento, con la
     * cantidad de apariciones. Se usan para completar las fechas a las que
     * el OCR les cortó el año.
     *
     * @var array<int, int>
     */
    private array $aniosDetectados = [];

    /**
     * Correcciones de OCR para digitos.
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
     * Convierte el XLSX al MISMO JSON normalizado que usa el flujo PDF/IA.
     *
     * El markdown es opcional: el XLSX de tablas no trae el titulo del
     * comprobante, asi que cuando el numero no aparece en las celdas se busca
     * ahi ("ORDEN DE PAGO OPL X 0000-00077740").
     *
     * @param  array<int|string, mixed>|string|null  $markdown
     * @return array<string, mixed>
     */
    public function documento(string $ruta, ?string $nombreOriginal = null, mixed $markdown = null): array
    {
        $crudo = $this->procesar($ruta, $nombreOriginal);
        $atenciones = [];
        $advertencias = [];
        $cabecera = [];
        $numeroDocumento = null;
        $totalInformado = null;
        $fechaDocumento = null;
        $tipo = 'detalle_liquidacion';
        $hojasConDatos = 0;
        $paginasLlamaParse = [];
        $atencionOrdenPago = null;
        $hayOrdenPago = false;
        $hayDetalleLiquidacion = false;

        foreach ($crudo['hojas'] as $indiceHoja => $hoja) {
            $filas = $hoja['datos'] ?? [];
            $indiceHeader = $this->buscarHeaderDetalle($filas);
            $esHojaDetalle = $indiceHeader !== null || $this->pareceHojaDetalle($filas);
            $enDetalle = $esHojaDetalle && $indiceHeader === null;
            $esLlamaParse = str_starts_with((string) ($hoja['nombre'] ?? ''), 'Page ');
            $esHojaValores = $this->esHojaValoresLlamaParse($filas);
            $esHojaComprobantes = $this->esHojaComprobantesLlamaParse($filas);
            $paginaHoja = $indiceHoja + 1;

            if ($esLlamaParse) {
                $paginaHoja = $this->paginaDeHoja($hoja['nombre'] ?? null, $paginaHoja);
                $paginasLlamaParse[$paginaHoja] = true;
            }

            $filasConDatos = 0;

            if ($this->esHojaOrdenPagoLlamaParse($filas)) {
                $hayOrdenPago = true;
                $this->procesarHojaOrdenPagoLlamaParse(
                    $filas,
                    $paginaHoja,
                    $atenciones,
                    $atencionOrdenPago,
                    $cabecera,
                    $numeroDocumento,
                    $fechaDocumento,
                    $totalInformado
                );
                $hojasConDatos++;
                continue;
            }

            if ($esHojaDetalle) {
                $hayDetalleLiquidacion = true;
            }

            if (($esLlamaParse || $esHojaValores || $esHojaComprobantes) && ! $esHojaDetalle) {
                $this->extraerCabeceraLlamaParseHoja(
                    $filas,
                    $cabecera,
                    $numeroDocumento,
                    $totalInformado,
                    $fechaDocumento,
                    $tipo
                );

                continue;
            }

            foreach ($filas as $i => $fila) {
                $celdas = $fila['celdas'] ?? [];
                $siguiente = $filas[$i + 1] ?? null;

                if (! $esHojaDetalle) {
                    $this->extraerCabeceraFila(
                        $celdas,
                        $siguiente,
                        $cabecera,
                        $numeroDocumento,
                        $totalInformado,
                        $fechaDocumento,
                        $tipo
                    );

                    continue;
                }

                if (! $enDetalle) {
                    if ($i === $indiceHeader) {
                        $enDetalle = true;
                        $tipo = 'detalle_liquidacion';

                        continue;
                    }

                    if (! $esLlamaParse) {
                        $this->extraerCabeceraFila(
                            $celdas,
                            $siguiente,
                            $cabecera,
                            $numeroDocumento,
                            $totalInformado,
                            $fechaDocumento,
                            $tipo
                        );
                    }

                    continue;
                }

                $textoFila = $this->normalizarTexto($this->textoFila($celdas));

                if (str_contains($textoFila, 'caja de prof')) {
                    break;
                }

                if ($this->esHeaderDetalle($celdas)) {
                    continue;
                }

                $atencion = $this->parseFilaDetalle($celdas, $paginaHoja);

                if ($atencion === null) {
                    continue;
                }

                $atenciones[] = $atencion;
                $filasConDatos++;
            }

            if ($filasConDatos > 0) {
                $hojasConDatos++;
            }
        }

        if ($atencionOrdenPago !== null) {
            $atenciones[] = $atencionOrdenPago;
            $atencionOrdenPago = null;
        }

        if ($hayOrdenPago) {
            $tipo = $hayDetalleLiquidacion ? 'detalle_liquidacion' : 'orden_pago';
        }

        if ($atenciones === []) {
            $advertencias[] = 'No se detectaron filas de detalle en el XLSX.';
        }

        if ($numeroDocumento === null) {
            $numeroDocumento = NumeroOrdenPagoExtractor::desdeMarkdown($markdown);

            if ($numeroDocumento !== null) {
                $advertencias[] = 'Numero de orden de pago tomado del markdown: '.$numeroDocumento.'.';
            }
        }

        // El OCR a veces corta el año de las fechas ("15/09/202"). Se toman
        // los años completos del propio documento y se completan.
        $this->registrarAniosDeHojas($crudo['hojas'] ?? []);
        $anioPredominante = $this->anioPredominante();

        if ($anioPredominante !== null) {
            $fechaDocumento = $this->completarAnioFecha($fechaDocumento, $anioPredominante);

            foreach ($atenciones as $indice => $atencion) {
                if (is_array($atencion) && isset($atencion['fecha'])) {
                    $atenciones[$indice]['fecha'] = $this->completarAnioFecha(
                        is_string($atencion['fecha']) ? $atencion['fecha'] : null,
                        $anioPredominante
                    );
                }
            }
        }

        $input = [
            'documento' => [
                'tipo' => $tipo,
                'numero' => $numeroDocumento,
                'fecha' => $fechaDocumento,
                'paginas' => $paginasLlamaParse !== [] ? max(array_keys($paginasLlamaParse)) : max(1, $hojasConDatos),
                'total_informado' => $totalInformado,
            ],
            'cabecera' => $cabecera,
            'atenciones' => $atenciones,
            'confianza' => null,
            'observaciones' => $advertencias === [] ? null : implode(' ', $advertencias),
            'advertencias' => $advertencias,
        ];

        return app(DocumentoValidator::class)->validar($input);
    }

    /**
     * @param  array<int, array<string, mixed>>  $celdas
     */
    private function esHeaderDetalle(array $celdas): bool
    {
        $texto = $this->normalizarTexto($this->textoFila($celdas));

        return str_contains($texto, 'atencion')
            && (str_contains($texto, 'valor liq')
                || str_contains($texto, 'importe')
                || str_contains($texto, 'valor'));
    }

    /**
     * Las hojas de continuación de LlamaParse no repiten el encabezado de
     * detalle: empiezan directamente con filas de atención.
     *
     * @param  array<int, array<string, mixed>>  $filas
     */
    private function buscarHeaderDetalle(array $filas): ?int
    {
        foreach ($filas as $i => $fila) {
            if ($this->esHeaderDetalle($fila['celdas'] ?? [])) {
                return $i;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $filas
     */
    private function pareceHojaDetalle(array $filas): bool
    {
        foreach ($filas as $fila) {
            $celdas = $fila['celdas'] ?? [];
            $noVacias = 0;

            foreach ($celdas as $celda) {
                if ($this->textoCelda($celda) !== '') {
                    $noVacias++;
                }
            }

            if ($noVacias < 7) {
                continue;
            }

            $tokens = $this->tokensFila($celdas);

            if ($tokens === []) {
                continue;
            }

            $importeIdx = $this->buscarIndiceImporte($tokens);

            if ($importeIdx === null) {
                continue;
            }

            $tieneFecha = false;

            for ($i = 0; $i < $importeIdx; $i++) {
                if ($this->buscarFechaEnToken($tokens[$i]) !== null) {
                    $tieneFecha = true;
                    break;
                }
            }

            if ($tieneFecha && $this->esTokenAtencion($tokens[0])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hoja "Valores" del XLSX de LlamaParse: el encabezado trae las columnas
     * Valor / Banco / Numero / Fecha / Importe, donde vienen las retenciones
     * (Caja de Prof., Gastos Administrativos, IB) y los cheques/transferencias.
     *
     * @param  array<int, array<string, mixed>>  $filas
     */
    private function esHojaValoresLlamaParse(array $filas): bool
    {
        $primera = $this->normalizarTexto($this->textoFila($filas[0]['celdas'] ?? []));

        return str_contains($primera, 'valor')
            && str_contains($primera, 'banco')
            && str_contains($primera, 'importe');
    }

    /**
     * Hoja "Comprobantes" del XLSX de LlamaParse: el encabezado trae
     * Comprobante / Fecha / Nº Liq. / Observaciones / Neto / IVA / Total /
     * Cancela / Saldo. De ahi sale el total de la liquidacion.
     *
     * @param  array<int, array<string, mixed>>  $filas
     */
    private function esHojaComprobantesLlamaParse(array $filas): bool
    {
        $primera = $this->normalizarTexto($this->textoFila($filas[0]['celdas'] ?? []));

        return str_contains($primera, 'comprobante')
            && (str_contains($primera, 'neto') || str_contains($primera, 'total'));
    }

    /**
     * @param  array<int, array<string, mixed>>  $filas
     */
    private function esHojaOrdenPagoLlamaParse(array $filas): bool
    {
        $primera = $this->normalizarTexto($this->textoFila($filas[0]['celdas'] ?? []));
        $segunda = $this->normalizarTexto($this->textoFila($filas[1]['celdas'] ?? []));

        return str_contains($primera, 'fecha practica')
            && (str_contains($primera, 'cod nom') || str_contains($segunda, 'cod nom'));
    }

    /**
     * Formato "Orden de Pago" de LlamaParse: cada práctica ocupa dos filas
     * (fecha/código/descripción y luego %/cantidad/neto/IVA/total) y la
     * atención puede continuar en otra hoja.
     *
     * @param  array<int, array<string, mixed>>  $filas
     * @param  array<int, array<string, mixed>>  $atenciones
     * @param  array<string, mixed>  $cabecera
     */
    private function procesarHojaOrdenPagoLlamaParse(
        array $filas,
        int $pagina,
        array &$atenciones,
        ?array &$atencionActual,
        array &$cabecera,
        ?string &$numeroDocumento,
        ?string &$fechaDocumento,
        mixed &$totalInformado
    ): void {
        $totalFilas = count($filas);

        for ($i = 0; $i < $totalFilas; $i++) {
            $celdas = $filas[$i]['celdas'] ?? [];
            $texto = $this->textoFila($celdas);
            $norm = $this->normalizarTexto($texto);

            if ($norm === ''
                || str_contains($norm, 'fecha practica')
                || str_contains($norm, 'nombre formula')
                || (str_contains($norm, 'formula') && str_contains($norm, 'cantidad'))) {
                continue;
            }

            if (preg_match('/agente\s*:\s*(.+)$/iu', $texto, $m) === 1) {
                $valor = trim($m[1]);

                if (preg_match('/^(\d{3,8})\s*-\s*(.+)$/u', $valor, $partes) === 1) {
                    $cabecera['profesional_codigo'] = $partes[1];
                    $cabecera['profesional'] = trim($partes[2]);
                } elseif (! isset($cabecera['profesional'])) {
                    $cabecera['profesional'] = $valor;
                }

                continue;
            }

            if ($numeroDocumento === null
                && str_contains($norm, 'liquidacion')
                && ! str_contains($norm, 'total')
                && preg_match('/liquidaci[oó]n\s*:?\s*(\d{3,})/iu', $texto, $m) === 1) {
                $numeroDocumento = $m[1];
                $cabecera['numero_liquidacion'] = $m[1];

                continue;
            }

            if (str_contains($norm, 'total atencion')) {
                if ($atencionActual !== null) {
                    $importe = $this->buscarImporteTokens($this->tokensFila($celdas));

                    if ($importe !== null) {
                        $centavos = ImporteCentavos::desdeTexto($importe)['centavos'];

                        if ($centavos !== null) {
                            $atencionActual['total_atencion'] = $centavos / 100;
                        }

                        $atencionActual['total_atencion_original'] = $importe;
                    }

                    $atencionActual['pagina_fin'] = $pagina;
                    $atenciones[] = $atencionActual;
                    $atencionActual = null;
                }

                continue;
            }

            if (str_contains($norm, 'total liquidacion') || str_contains($norm, 'total general')) {
                continue;
            }

            if (str_contains($norm, 'atencion') && ! str_contains($norm, 'total')) {
                if ($atencionActual !== null) {
                    $atencionActual['pagina_fin'] = $pagina;
                    $atenciones[] = $atencionActual;
                }

                $atencionActual = [
                    'obra_social' => null,
                    'plan' => null,
                    'atencion' => null,
                    'atencion_original' => null,
                    'atencion_revisar' => false,
                    'fecha' => null,
                    'numero_afiliado' => null,
                    'numero_afiliado_original' => null,
                    'numero_afiliado_revisar' => false,
                    'nombre_afiliado' => null,
                    'total_atencion' => null,
                    'total_atencion_original' => null,
                    'total_atencion_revisar' => false,
                    'pagina_inicio' => $pagina,
                    'pagina_fin' => $pagina,
                    'detalles' => [],
                ];

                $this->aplicarMetadatosOrdenPago($celdas, $atencionActual);

                continue;
            }

            if ($atencionActual !== null
                && (str_contains($norm, 'afiliado')
                    || str_contains($norm, 'nombre')
                    || str_contains($norm, 'obra social')
                    || str_contains($norm, 'plan'))) {
                $this->aplicarMetadatosOrdenPago($celdas, $atencionActual);

                continue;
            }

            if ($atencionActual !== null) {
                $detalle = $this->parsearDetalleOrdenPago($filas, $i, $pagina);

                if ($detalle !== null) {
                    $atencionActual['detalles'][] = $detalle['detalle'];
                    $atencionActual['pagina_fin'] = $pagina;
                    $i = $detalle['consumidas'];
                }
            }
        }
    }

    /**
     * Descripcion de la practica. En el formato "Detalle de Liquidacion" la
     * celda siguiente al codigo puede traer el tipo de formula ("Esp") en
     * lugar de la descripcion.
     */
    private function descripcionDetalleOrdenPago(string $texto): string
    {
        $texto = trim($texto);

        if ($texto === '' || $this->normalizarTexto($texto) === 'esp') {
            return '';
        }

        return $texto;
    }

    /**
     * @param  array<int, array<string, mixed>>  $celdas
     * @return array{consumidas: int, detalle: array<string, mixed>}|null
     */
    private function parsearDetalleOrdenPago(array $filas, int $indice, int $pagina): ?array
    {
        $celdas = $filas[$indice]['celdas'] ?? [];
        $textoA = $this->textoCelda($celdas[0] ?? []);
        $textoB = $this->textoCelda($celdas[1] ?? []);
        $textoC = $this->textoCelda($celdas[2] ?? []);
        $norm = $this->normalizarTexto($this->textoFila($celdas));

        if (str_contains($norm, 'total')
            || str_contains($norm, 'atencion')
            || str_contains($norm, 'liquidacion')
            || str_contains($norm, 'agente')) {
            return null;
        }

        $fecha = $this->buscarFechaEnToken($textoA)
            ?? $this->buscarFechaEnToken($textoB)
            ?? $this->buscarFechaEnToken($textoC);

        if ($fecha === null) {
            return null;
        }

        $codigo = null;
        $descripcion = null;

        if (preg_match('/^[0-9OoQIlZSGBgbTq]{3,8}$/u', trim($textoB)) === 1
            && $this->normalizarTexto($textoB) !== 'esp') {
            // Layout con la fecha, el codigo y la descripcion en celdas
            // separadas ("19/08/2026" | "600011" | "Especial F").
            $codigo = trim($textoB);
            $descripcion = trim($textoC);
        } elseif (preg_match('/^(\d{1,2}\s*[\/.\-]\s*\d{1,2}\s*[\/.\-]\s*\d{2,4})\s+([0-9OoQIlZSGBgbTq]{3,8})\s+(.+)$/u', $textoA, $m) === 1) {
            $fecha = $this->buscarFechaEnToken($m[1]) ?? $fecha;
            $codigo = $m[2];
            $descripcion = trim($m[3]);
        } elseif (preg_match('/^(\d{1,2}\s*[\/.\-]\s*\d{1,2}\s*[\/.\-]\s*\d{2,4})\s+([0-9OoQIlZSGBgbTq]{3,8})$/u', $textoA, $m) === 1) {
            // Layout con la fecha y el codigo juntos en la primera celda y la
            // descripcion en la siguiente ("18/08/2026 600023" | "Evaluacion").
            $fecha = $this->buscarFechaEnToken($m[1]) ?? $fecha;
            $codigo = $m[2];
            $descripcion = $this->descripcionDetalleOrdenPago($textoB);
        }

        if ($codigo === null) {
            return null;
        }

        $consumidas = $indice;
        $porcentaje = null;
        $cantidad = null;
        $importe = null;

        if (isset($filas[$indice + 1])) {
            $cont = $filas[$indice + 1]['celdas'] ?? [];
            $textoCont = $this->textoFila($cont);
            $tokensCont = $this->tokensFila($cont);
            $importe = $this->buscarImporteTokens($tokensCont);

            if ($importe !== null) {
                if (preg_match('/(\d{1,3}(?:[.,]\d+)?)\s*%/', $textoCont, $m) === 1) {
                    $porcentaje = (float) str_replace(',', '.', $m[1]);
                }

                // En los dos layouts de este formato la cantidad esta en la
                // columna C (la misma que la descripcion en el layout con la
                // fecha y el codigo juntos).
                $cantidad = $this->textoCelda($cont[2] ?? []);

                if ($cantidad === '' || preg_match('/^\d{1,5}(?:[.,]\d{1,2})?$/', trim($cantidad)) !== 1) {
                    $cantidad = '';

                    foreach ($tokensCont as $token) {
                        if (preg_match('/^\d{1,5}(?:[.,]\d{1,2})?$/', trim($token)) === 1) {
                            $cantidad = trim($token);
                            break;
                        }
                    }
                }

                $consumidas = $indice + 1;
            }
        }

        return [
            'consumidas' => $consumidas,
            'detalle' => [
                'fecha' => $fecha,
                'codigo_nivel' => $codigo,
                'descripcion_nivel' => $descripcion === '' ? null : $descripcion,
                'periodo' => null,
                'porcentaje_cobertura' => $porcentaje,
                'cantidad' => $cantidad === '' ? null : $cantidad,
                'importe' => $importe,
                'pagina' => $pagina,
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $celdas
     * @param  array<string, mixed>  $atencion
     */
    private function aplicarMetadatosOrdenPago(array $celdas, array &$atencion): void
    {
        $textos = [];

        foreach ($celdas as $celda) {
            $texto = str_replace(['<br/>', '<br />', '<br>'], "\n", $this->textoCelda($celda));

            foreach (preg_split('/\R/u', $texto) ?: [] as $linea) {
                $linea = trim($linea);

                if ($linea !== '') {
                    $textos[] = $linea;
                }
            }
        }

        $consumidas = [];

        foreach ($textos as $i => $linea) {
            if (isset($consumidas[$i])) {
                continue;
            }

            [$etiqueta, $valor] = $this->separarEtiquetaOrdenPago($linea);

            if ($etiqueta === null) {
                continue;
            }

            if ($valor === '' && isset($textos[$i + 1])) {
                [$siguienteEtiqueta] = $this->separarEtiquetaOrdenPago($textos[$i + 1]);

                if ($siguienteEtiqueta === null) {
                    $valor = $textos[$i + 1];
                    $consumidas[$i + 1] = true;
                }
            }

            $this->asignarMetadatoOrdenPago($etiqueta, $valor, $atencion);
        }
    }

    /**
     * @return array{0: string|null, 1: string}
     */
    private function separarEtiquetaOrdenPago(string $texto): array
    {
        $texto = trim($texto);

        $patrones = [
            'atencion' => '/^atenci[oó]n\s*:?\s*(.*)$/iu',
            'afiliado' => '/^(?:n[º°o]?|nro\.?|numero)\s*afiliado\s*:?\s*(.*)$/iu',
            'fecha' => '/^fecha\s*:?\s*(.*)$/iu',
            'nombre' => '/^nombre\s+afiliado\s*:?\s*(.*)$/iu',
            'obra_social' => '/^obra\s+social(?:\s*-\s*plan)?\s*:?\s*(.*)$/iu',
            'plan' => '/^plan\s*:?\s*(.*)$/iu',
            'area' => '/^[aá]rea\s*:?\s*(.*)$/iu',
        ];

        foreach ($patrones as $etiqueta => $patron) {
            if (preg_match($patron, $texto, $m) === 1) {
                return [$etiqueta, trim($m[1])];
            }
        }

        return [null, $texto];
    }

    /**
     * @param  array<string, mixed>  $atencion
     */
    private function asignarMetadatoOrdenPago(string $etiqueta, string $valor, array &$atencion): void
    {
        $valor = trim($valor);

        if ($valor === '') {
            return;
        }

        if ($etiqueta === 'atencion' && $atencion['atencion'] === null) {
            $atencion['atencion'] = $valor;
            $atencion['atencion_original'] = $valor;
        } elseif ($etiqueta === 'afiliado' && $atencion['numero_afiliado'] === null) {
            $digitos = preg_replace('/\D+/', '', $valor) ?? '';

            if ($digitos !== '') {
                $atencion['numero_afiliado'] = $digitos;
                $atencion['numero_afiliado_original'] = $digitos;
            }
        } elseif ($etiqueta === 'fecha' && $atencion['fecha'] === null) {
            $fecha = $this->buscarFechaEnToken($valor);

            if ($fecha !== null) {
                $atencion['fecha'] = $fecha;
            }
        } elseif ($etiqueta === 'nombre' && $atencion['nombre_afiliado'] === null) {
            $atencion['nombre_afiliado'] = $valor;
        } elseif ($etiqueta === 'obra_social' && $atencion['obra_social'] === null) {
            $partes = preg_split('/\s+-\s+/u', $valor, 2) ?: [$valor];
            $atencion['obra_social'] = trim($partes[0]);

            if (isset($partes[1]) && $atencion['plan'] === null) {
                $atencion['plan'] = trim($partes[1]);
            }
        } elseif ($etiqueta === 'plan' && $atencion['plan'] === null) {
            $atencion['plan'] = $valor;
        }
    }

    /**
     * El XLSX de LlamaParse separa la cabecera en varias hojas: agente,
     * comprobantes, valores/retenciones y resumen. No usa el mismo layout
     * que el XLSX armado a mano, por eso se extrae por hoja.
     *
     * @param  array<int, array<string, mixed>>  $filas
     * @param  array<string, mixed>  $cabecera
     */
    private function extraerCabeceraLlamaParseHoja(
        array $filas,
        array &$cabecera,
        ?string &$numeroDocumento,
        mixed &$totalInformado,
        ?string &$fechaDocumento,
        string &$tipo
    ): void {
        $primera = $filas[0]['celdas'] ?? [];
        $titulo = $this->normalizarTexto($this->textoFila($primera));

        if (str_contains($titulo, 'agente') && str_contains($titulo, 'cuit')) {
            $datos = $filas[1]['celdas'] ?? [];
            $profesional = $this->textoCelda($datos[0] ?? []);

            if ($profesional !== '') {
                $cabecera['profesional'] = $this->limpiarCabecera($profesional);
            }

            $cuit = preg_replace('/\D+/', '', $this->textoCelda($datos[1] ?? [])) ?? '';

            if ($cuit !== '') {
                $cabecera['cuit'] = $cuit;
                $cabecera['cuit_valido'] = $this->cuitValido($cuit);
            }

            $fecha = $this->buscarFechaEnToken($this->textoCelda($datos[3] ?? []));

            if ($fechaDocumento === null
                && $fecha !== null
                && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fecha) === 1) {
                $fechaDocumento = $fecha;
            }

            foreach ($filas as $i => $fila) {
                $texto = $this->normalizarTexto($this->textoFila($fila['celdas'] ?? []));

                if (str_contains($texto, 'cond') && str_contains($texto, 'iva')) {
                    $valor = $this->textoCelda($filas[$i + 1]['celdas'][0] ?? []);

                    if ($valor !== '') {
                        $cabecera['condicion_iva'] = $this->limpiarCabecera($valor);
                    }
                }
            }

            return;
        }

        if (str_contains($titulo, 'comprobante') && str_contains($titulo, 'neto')) {
            $primeraData = $filas[1]['celdas'] ?? [];
            $comprobante = $this->textoCelda($primeraData[0] ?? []);

            if ($comprobante !== '') {
                $cabecera['comprobante'] = $this->limpiarCabecera($comprobante);
            }

            // Fecha del comprobante. El OCR a veces corta el año ("15/09/202"),
            // en ese caso se completa con un año completo del documento.
            if ($fechaDocumento === null) {
                $encabezado = array_map(
                    fn ($celda) => $this->normalizarTexto($this->textoCelda($celda)),
                    $filas[0]['celdas'] ?? []
                );
                $indiceFecha = $this->indiceEtiqueta($encabezado, 'fecha');

                if ($indiceFecha !== null) {
                    $fecha = $this->buscarFechaEnToken($this->textoCelda($primeraData[$indiceFecha] ?? []));

                    if ($fecha !== null) {
                        $fechaDocumento = $fecha;
                    }
                }
            }

            // El total de la liquidacion esta en la columna "Cancela" (la
            // ultima fila con importe): en los comprobantes cancelados cada
            // liquidacion tiene su importe y el ultimo es la suma.
            $total = null;

            foreach ($filas as $fila) {
                $celdas = $fila['celdas'] ?? [];
                $valor = $this->textoCelda($celdas[7] ?? []);

                if ($valor !== '' && $this->esImporteMonetario($valor)) {
                    $total = $valor;
                }
            }

            if ($total !== null) {
                $totalInformado = $total;
                $cabecera['importe'] = $total;
                $cabecera['importe_original'] = $total;
            }

            return;
        }

        if (str_contains($titulo, 'valor') && str_contains($titulo, 'banco') && str_contains($titulo, 'importe')) {
            $encabezado = array_map(
                fn ($celda) => $this->normalizarTexto($this->textoCelda($celda)),
                $filas[0]['celdas'] ?? []
            );
            $indiceImporte = $this->indiceEtiqueta($encabezado, 'importe');
            $caja = null;
            $gastos11 = null;
            $gastos5 = null;
            $ib = null;
            $transferencia = 0;

            foreach ($filas as $fila) {
                $celdas = $fila['celdas'] ?? [];
                $texto = $this->normalizarTexto($this->textoFila($celdas));
                $importe = null;

                if ($indiceImporte !== null) {
                    $valorImporte = $this->textoCelda($celdas[$indiceImporte] ?? []);

                    if ($valorImporte !== '' && $this->esImporteMonetario($valorImporte)) {
                        $importe = $valorImporte;
                    }
                }

                $importe ??= $this->buscarImporteTokens($this->tokensFila($celdas));

                if ($importe === null) {
                    continue;
                }

                $centavos = ImporteCentavos::desdeTexto($importe)['centavos'];

                if ($centavos === null) {
                    continue;
                }

                if (str_contains($texto, 'caja de prof')) {
                    $caja = $centavos;
                } elseif (str_contains($texto, 'gastos administrativos') && str_contains($texto, '11')) {
                    $gastos11 = $centavos;
                } elseif (str_contains($texto, 'gastos administrativos') && str_contains($texto, '5')) {
                    $gastos5 = $centavos;
                } elseif (str_contains($texto, 'ingresos brutos')
                    || preg_match('/\bib\b/u', $texto) === 1
                    || preg_match('/\bi b\b/u', $texto) === 1) {
                    $ib = $centavos;
                } elseif (preg_match('/\bch\b/', $texto) === 1
                    || str_contains($texto, 'cheque')
                    || str_contains($texto, 'transf')) {
                    $transferencia += $centavos;

                    if ($fechaDocumento === null) {
                        $fecha = $this->buscarFechaTokens($this->tokensFila($celdas));

                        if ($fecha !== null && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fecha) === 1) {
                            $fechaDocumento = $fecha;
                        }
                    }
                }
            }

            $totalRetenciones = ($caja ?? 0) + ($gastos11 ?? 0) + ($gastos5 ?? 0);

            if ($caja !== null) {
                $cabecera['retencion_caja_centavos'] = $caja;
            }

            if ($gastos11 !== null) {
                $cabecera['retencion_gastos_11_centavos'] = $gastos11;
            }

            if ($gastos5 !== null) {
                $cabecera['retencion_gastos_5_centavos'] = $gastos5;
            }

            if ($ib !== null) {
                $cabecera['retencion_ib_centavos'] = $ib;
            }

            if ($totalRetenciones > 0) {
                $cabecera['total_retenciones_centavos'] = $totalRetenciones;
            }

            if ($transferencia > 0) {
                $cabecera['transferencia_centavos'] = $transferencia;
            }
        }
    }

    private function paginaDeHoja(?string $nombre, int $default): int
    {
        if (preg_match('/^Page\s+(\d+)/i', (string) $nombre, $m) === 1) {
            return (int) $m[1];
        }

        return $default;
    }

    private function cuitValido(string $cuit): bool
    {
        if (preg_match('/^\d{11}$/', $cuit) !== 1) {
            return false;
        }

        $pesos = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;

        for ($i = 0; $i < 10; $i++) {
            $suma += (int) $cuit[$i] * $pesos[$i];
        }

        $resto = $suma % 11;
        $verificador = $resto === 0 ? 0 : ($resto === 1 ? 9 : 11 - $resto);

        return $verificador === (int) $cuit[10];
    }

    /**
     * @param  array<int, array<string, mixed>>  $celdas
     * @param  array<string, mixed>|null  $siguiente
     * @param  array<string, mixed>  $cabecera
     */
    private function extraerCabeceraFila(
        array $celdas,
        ?array $siguiente,
        array &$cabecera,
        ?string &$numeroDocumento,
        mixed &$totalInformado,
        ?string &$fechaDocumento,
        string &$tipo
    ): void {
        $textos = $this->textosFila($celdas);
        $joined = trim(implode(' ', array_filter($textos, fn ($t) => $t !== '')));
        $normalizado = $this->normalizarTexto($joined);
        $siguienteFila = $this->primerTextoDeFila($siguiente);
        $tokens = $this->tokensFila($celdas);
        $textosNormalizados = array_map(fn (string $texto) => $this->normalizarTexto($texto), $textos);
        $filaConAgente = str_contains($normalizado, 'agente');
        $filaConCuit = $this->indiceEtiqueta($textosNormalizados, 'cuit') !== null;

        if (str_contains($normalizado, 'detalle liquidacion')) {
            $tipo = 'detalle_liquidacion';
        } elseif (str_contains($normalizado, 'orden de pago')) {
            $tipo = 'orden_pago';
        }

        if ($filaConAgente || $filaConCuit) {
            $this->extraerDatosAgente($textos, $siguiente, $cabecera, $fechaDocumento);
        }

        if (str_contains($normalizado, 'concepto') && ! isset($cabecera['concepto'])) {
            $valorConcepto = $this->valorDespuesDe($textos, 'concepto') ?? $siguienteFila;

            if (! $this->esEtiquetaDeEncabezado($valorConcepto)) {
                $cabecera['concepto'] = $this->limpiarCabecera($valorConcepto);
            }
        }

        if ($fechaDocumento === null) {
            $fechaDocumento = $this->buscarFechaTokens($tokens);
        }

        if ($numeroDocumento === null
            && preg_match('/(?:opl|orden\s+de\s+pago)[^0-9]*((?:x\s*)?[0-9][0-9\-\s]{4,})/i', $joined, $m) === 1) {
            $numeroDocumento = trim($m[1]);
        }

        $importe = $this->buscarImporteTokens($tokens);

        if ($importe === null) {
            return;
        }

        if (str_contains($normalizado, 'neto')
            || (str_contains($normalizado, 'importe') && $totalInformado === null)
            || (str_contains($normalizado, 'total') && $totalInformado === null)) {
            $totalInformado = $importe;
            $cabecera['importe'] = $importe;
        }
    }

    /**
     * Extrae el agente y el CUIT de la tabla "Agente | CUIT | Direccion |
     * Fecha", donde los valores estan en la fila siguiente al encabezado.
     * Tambien soporta el formato "Agente: 123 - NOMBRE" en una misma fila.
     *
     * @param  array<int, string>  $textos
     * @param  array<string, mixed>|null  $siguiente
     * @param  array<string, mixed>  $cabecera
     */
    private function extraerDatosAgente(
        array $textos,
        ?array $siguiente,
        array &$cabecera,
        ?string &$fechaDocumento
    ): void {
        $normalizados = array_map(fn (string $texto) => $this->normalizarTexto($texto), $textos);
        $columnaCuit = $this->indiceEtiqueta($normalizados, 'cuit');

        if ($columnaCuit !== null && $siguiente !== null) {
            $valores = $this->textosFila($siguiente['celdas'] ?? []);
            $columnaAgente = $this->indiceEtiqueta($normalizados, 'agente') ?? 0;

            if (! isset($cabecera['profesional'])) {
                $nombre = $this->limpiarCabecera($valores[$columnaAgente] ?? null);

                if ($nombre !== null && ! $this->esEtiquetaDeEncabezado($nombre)) {
                    $cabecera['profesional'] = $nombre;
                }
            }

            if (! isset($cabecera['cuit'])) {
                $cuit = $this->soloDigitos($valores[$columnaCuit] ?? null);

                if ($cuit !== null) {
                    $cabecera['cuit'] = $cuit;
                    $cabecera['cuit_valido'] = $this->cuitValido($cuit);
                }
            }

            $columnaFecha = $this->indiceEtiqueta($normalizados, 'fecha');

            if ($fechaDocumento === null && $columnaFecha !== null) {
                $fecha = $this->buscarFechaEnToken($valores[$columnaFecha] ?? '');

                if ($fecha !== null && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fecha) === 1) {
                    $fechaDocumento = $fecha;
                }
            }

            return;
        }

        if (isset($cabecera['profesional'])) {
            return;
        }

        $valor = $this->valorDespuesDe($textos, 'agente');

        if ($valor === null || $this->esEtiquetaDeEncabezado($valor)) {
            return;
        }

        if (preg_match('/^(\d{3,8})\s*-\s*(.+)$/u', $valor, $partes) === 1) {
            $cabecera['profesional_codigo'] = $partes[1];
            $cabecera['profesional'] = trim($partes[2]);

            return;
        }

        $cabecera['profesional'] = $this->limpiarCabecera($valor);
    }

    /**
     * @param  array<int, string>  $textos
     */
    private function indiceEtiqueta(array $textos, string $etiqueta): ?int
    {
        foreach ($textos as $i => $texto) {
            if ($texto === $etiqueta) {
                return $i;
            }
        }

        return null;
    }

    private function esEtiquetaDeEncabezado(?string $valor): bool
    {
        if ($valor === null) {
            return false;
        }

        return in_array($this->normalizarTexto($valor), [
            'agente',
            'cuit',
            'direccion',
            'fecha',
            'concepto',
            'importe',
            'neto',
            'iva',
            'total',
            'nombre',
            'apellido',
            'domicilio',
            'cond iva',
            'comprobante',
            'observaciones',
            'numero',
        ], true);
    }

    private function soloDigitos(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $digitos = (string) preg_replace('/\D+/', '', $valor);

        return $digitos === '' ? null : $digitos;
    }

    private function limpiarCabecera(?string $texto): ?string
    {
        if ($texto === null) {
            return null;
        }

        $partes = preg_split('/\s{2,}/', trim($texto));

        if (is_array($partes) && $partes !== []) {
            $texto = trim($partes[0]);
        }

        return $texto === '' ? null : $texto;
    }

    /**
     * @param  array<int, array<string, mixed>>  $celdas
     * @return array<string, mixed>|null
     */
    private function parseFilaDetalle(array $celdas, int $pagina): ?array
    {
        $tokens = $this->tokensFila($celdas);

        if ($tokens === []) {
            return null;
        }

        if (str_starts_with($this->normalizarTexto($tokens[0]), 'liq')) {
            return null;
        }

        $textoFila = $this->normalizarTexto(implode(' ', $tokens));

        foreach ([
            'total liquidacion',
            'total general',
            'comprobantes cancelados',
            'caja de prof',
            'fecha de emision',
        ] as $ignorar) {
            if (str_contains($textoFila, $ignorar)) {
                return null;
            }
        }

        $importeIdx = $this->buscarIndiceImporte($tokens);

        if ($importeIdx === null) {
            return null;
        }

        $fechaIdx = null;
        $fecha = null;

        for ($i = 0; $i < $importeIdx; $i++) {
            $fechaToken = $this->buscarFechaEnToken($tokens[$i]);

            if ($fechaToken !== null) {
                $fechaIdx = $i;
                $fecha = $fechaToken;

                break;
            }
        }

        $atencionIdx = null;
        $atencion = null;
        $limiteAtencion = $fechaIdx ?? $importeIdx;

        for ($i = 0; $i < $limiteAtencion; $i++) {
            if ($this->esTokenAtencion($tokens[$i])) {
                $atencionIdx = $i;
                $atencion = $tokens[$i];

                break;
            }
        }

        if ($atencion === null && $fecha === null) {
            return null;
        }

        $periodoIdx = null;
        $periodo = null;

        for ($i = 0; $i < $importeIdx; $i++) {
            if ($i === $atencionIdx) {
                continue;
            }

            $periodoToken = $this->normalizarPeriodoToken($tokens[$i]);

            if ($periodoToken !== null) {
                $periodoIdx = $i;
                $periodo = $periodoToken;

                break;
            }
        }

        $osIdx = null;
        $obraSocial = null;

        for ($i = 0; $i < $importeIdx; $i++) {
            if (in_array($i, [$atencionIdx, $periodoIdx, $fechaIdx], true)) {
                continue;
            }

            if ($this->esTokenObraSocial($tokens[$i]) || $this->esTokenObraSocialSinNumero($tokens[$i])) {
                $osIdx = $i;
                $obraSocial = $tokens[$i];

                break;
            }
        }

        if ($osIdx !== null && $periodoIdx !== null) {
            for ($i = $osIdx + 1; $i < $periodoIdx; $i++) {
                if (! $this->esContinuacionObraSocial($tokens[$i])) {
                    break;
                }

                $obraSocial .= ' '.$tokens[$i];
            }
        }

        $tipoNomIdx = null;

        for ($i = 0; $i < $importeIdx; $i++) {
            if (in_array($i, [$atencionIdx, $periodoIdx, $osIdx, $fechaIdx], true)) {
                continue;
            }

            if ($this->esTipoNom($tokens[$i])) {
                $tipoNomIdx = $i;

                break;
            }
        }

        $codigoIdx = null;
        $codigo = null;
        $inicioCodigo = ($fechaIdx ?? $atencionIdx ?? -1) + 1;

        for ($i = $inicioCodigo; $i < $importeIdx; $i++) {
            if (in_array($i, [$atencionIdx, $periodoIdx, $osIdx], true)) {
                continue;
            }

            if ($this->esTokenCodigo($tokens[$i], $atencion)) {
                $codigoIdx = $i;
                $codigo = $tokens[$i];

                break;
            }
        }

        $inicioPaciente = ($fechaIdx ?? $atencionIdx ?? -1) + 1;
        $finPaciente = $this->menorIndice([$osIdx, $periodoIdx, $tipoNomIdx, $codigoIdx, $importeIdx]);
        $pacienteTokens = [];

        for ($i = $inicioPaciente; $i < $finPaciente; $i++) {
            if (in_array($i, [$atencionIdx, $periodoIdx, $osIdx, $tipoNomIdx, $codigoIdx], true)) {
                continue;
            }

            if ($this->buscarFechaEnToken($tokens[$i]) !== null || $this->esImporteMonetario($tokens[$i])) {
                continue;
            }

            $pacienteTokens[] = $tokens[$i];
        }

        $paciente = $pacienteTokens === [] ? null : implode(' ', $pacienteTokens);

        if ($paciente === null && $fechaIdx !== null) {
            $paciente = $this->textoRestoFecha($tokens[$fechaIdx]);
        }

        $descripcion = null;

        if ($codigoIdx !== null) {
            $descripcionTokens = [];

            for ($i = $codigoIdx + 1; $i < $importeIdx; $i++) {
                if (in_array($i, [$atencionIdx, $periodoIdx, $osIdx, $tipoNomIdx], true)) {
                    continue;
                }

                if ($this->buscarFechaEnToken($tokens[$i]) !== null || $this->esImporteMonetario($tokens[$i])) {
                    continue;
                }

                $descripcionTokens[] = $tokens[$i];
            }

            $descripcion = $descripcionTokens === [] ? null : implode(' ', $descripcionTokens);
        }

        return $this->construirAtencion(
            atencion: $atencion,
            fecha: $fecha,
            paciente: $paciente,
            obraSocial: $obraSocial,
            periodo: $periodo,
            codigo: $codigo,
            descripcion: $descripcion,
            importe: $tokens[$importeIdx],
            pagina: $pagina
        );
    }

    private function textoRestoFecha(string $token): ?string
    {
        if (preg_match('/^\d{1,2}\s*[\/.\-]\s*[0-9OoIlSsBbGgZz]{1,2}\s*[\/.\-]\s*\d{2,4}\s+(.+)$/u', $token, $m) !== 1) {
            return null;
        }

        $resto = trim($m[1]);

        return $resto === '' ? null : $resto;
    }

    /**
     * @param  array<int, int|null>  $indices
     */
    private function menorIndice(array $indices): int
    {
        $validos = array_values(array_filter($indices, fn ($valor) => $valor !== null));

        return $validos === [] ? PHP_INT_MAX : min($validos);
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private function buscarIndiceImporte(array $tokens): ?int
    {
        $candidatos = [];

        foreach ($tokens as $i => $token) {
            if ($this->esImporteMonetario($token)) {
                $candidatos[] = $i;
            }
        }

        if ($candidatos === []) {
            $ultimo = count($tokens) - 1;
            $token = trim($tokens[$ultimo] ?? '');

            if ($ultimo > 0
                && preg_match('/^\$?\s*\d{1,5}$/', $token) === 1
                && $this->normalizarPeriodoToken($token) === null) {
                return $ultimo;
            }

            return null;
        }

        for ($j = count($candidatos) - 1; $j >= 0; $j--) {
            if (preg_match('/^0+([.,]0+)?$/', trim($tokens[$candidatos[$j]])) !== 1) {
                return $candidatos[$j];
            }
        }

        return $candidatos[count($candidatos) - 1];
    }

    private function esImporteMonetario(string $texto): bool
    {
        return preg_match('/^[-\s]*\$?\s*\d[\d.,]*[.,]\d{1,}\s*-?$/', trim($texto)) === 1;
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private function buscarImporteTokens(array $tokens): ?string
    {
        $indice = $this->buscarIndiceImporte($tokens);

        return $indice === null ? null : $tokens[$indice];
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private function buscarFechaTokens(array $tokens): ?string
    {
        foreach ($tokens as $token) {
            $fecha = $this->buscarFechaEnToken($token);

            if ($fecha !== null) {
                return $fecha;
            }
        }

        return null;
    }

    private function buscarFechaEnToken(string $token): ?string
    {
        if (preg_match('/(\d{1,2})\s*[\/.\-]\s*([0-9OoIlSsBbGgZzDd]{1,2})\s*[\/.\-]\s*(\d{2,4})/u', $token, $m) !== 1) {
            return null;
        }

        $dia = preg_replace('/\D+/', '', $this->corregirDigitosOcr($m[1])) ?? '';
        $mes = preg_replace('/\D+/', '', $this->corregirDigitosOcr($m[2])) ?? '';
        $anio = $m[3];

        if (strlen($anio) === 2) {
            $anio = '20'.$anio;
        }

        if ($dia === '' || $mes === '') {
            return trim($m[0]);
        }

        $diaNumero = (int) $dia;
        $mesNumero = (int) $mes;

        if (strlen($anio) === 3) {
            // Año cortado por el OCR ("15/09/202"): se devuelve tal cual y
            // luego se completa con un año del propio documento.
            return sprintf('%02d/%02d/%s', $diaNumero, $mesNumero, $anio);
        }

        if (! checkdate($mesNumero, $diaNumero, (int) $anio)) {
            return trim($m[0]);
        }

        return sprintf('%02d/%02d/%04d', $diaNumero, $mesNumero, (int) $anio);
    }

    /**
     * Registra los años (4 dígitos) que aparecen en las fechas del documento.
     *
     * Sirve para completar las fechas a las que el OCR les cortó el año
     * ("15/09/202"), que se completan con el año predominante del documento.
     */
    private function registrarAniosEnTexto(?string $texto): void
    {
        if ($texto === null || $texto === '') {
            return;
        }

        if (preg_match_all('/\d{1,2}\s*[\/.\-]\s*\d{1,2}\s*[\/.\-]\s*(\d{4})/u', $texto, $matches) === false) {
            return;
        }

        foreach ($matches[1] as $anio) {
            $anio = (int) $anio;

            $this->aniosDetectados[$anio] = ($this->aniosDetectados[$anio] ?? 0) + 1;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $hojas
     */
    private function registrarAniosDeHojas(array $hojas): void
    {
        foreach ($hojas as $hoja) {
            foreach ($hoja['datos'] ?? [] as $fila) {
                foreach ($fila['celdas'] ?? [] as $celda) {
                    $this->registrarAniosEnTexto($this->textoCelda($celda));
                }
            }
        }
    }

    /**
     * Año que predomina en las fechas del documento.
     */
    private function anioPredominante(): ?int
    {
        if ($this->aniosDetectados === []) {
            return null;
        }

        arsort($this->aniosDetectados);

        return (int) array_key_first($this->aniosDetectados);
    }

    /**
     * Completa el año de las fechas a las que el OCR les cortó el último
     * dígito ("15/09/202" -> "15/09/2026").
     */
    private function completarAnioFecha(?string $fecha, ?int $anio): ?string
    {
        if ($fecha === null || $anio === null) {
            return $fecha;
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{3})$/', trim($fecha), $m) !== 1) {
            return $fecha;
        }

        return sprintf('%s/%s/%04d', $m[1], $m[2], $anio);
    }

    private function normalizarPeriodoToken(string $token): ?string
    {
        $token = trim($token);

        if (preg_match('/^((?:19|20)\d{2}(?:0[1-9]|1[0-2]))$/', $token, $m) === 1) {
            return $m[1];
        }

        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $token, $m) === 1) {
            $mes = (int) $m[1];

            if ($mes >= 1 && $mes <= 12) {
                return $m[2].str_pad((string) $mes, 2, '0', STR_PAD_LEFT);
            }
        }

        return null;
    }

    private function esTokenAtencion(string $token): bool
    {
        $token = trim($token);

        if ($token === '' || preg_match('/^[0-9OoQIlZSGBgbTq\s:.,-]+$/u', $token) !== 1) {
            return false;
        }

        $digitos = preg_replace('/\D+/', '', $this->corregirDigitosOcr($token)) ?? '';

        if (strlen($digitos) < 5 || strlen($digitos) > 10) {
            return false;
        }

        return $this->normalizarPeriodoToken($digitos) === null;
    }

    private function esTokenCodigo(string $token, ?string $atencion): bool
    {
        $token = trim($token);

        if (preg_match('/^\d{4,7}$/', $token) !== 1) {
            return false;
        }

        if ($this->normalizarPeriodoToken($token) !== null) {
            return false;
        }

        if ($atencion !== null) {
            $atencionDigitos = preg_replace('/\D+/', '', $this->corregirDigitosOcr($atencion)) ?? '';

            if ($atencionDigitos === $token) {
                return false;
            }
        }

        return true;
    }

    private function esTokenObraSocial(string $token): bool
    {
        $token = trim($token);

        if ($this->buscarFechaEnToken($token) !== null) {
            return false;
        }

        return preg_match(
            '/^[·.]?\s*(?=[0-9OoIlSsBbGg]*[0-9])[0-9OoIlSsBbGg]{1,4}(?:\s*[-·.]\s*){0,3}[A-Za-zÁÉÍÓÚÜÑáéíóúüñ].*$/u',
            $token
        ) === 1;
    }

    private function esTokenObraSocialSinNumero(string $token): bool
    {
        $normalizado = $this->normalizarTexto($token);

        return str_starts_with($normalizado, 'os ')
            || str_contains($normalizado, 'obra social');
    }

    private function esContinuacionObraSocial(string $token): bool
    {
        $token = trim($token);

        if ($token === '' || $this->esTipoNom($token) || $this->esImporteMonetario($token)) {
            return false;
        }

        return preg_match('/^[A-ZÁÉÍÓÚÜÑÑ.\s]+$/u', $token) === 1;
    }

    private function esTipoNom(string $texto): bool
    {
        $texto = $this->normalizarTexto($texto);

        return in_array($texto, ['anestesia', 'ioma', 'joma', 'os'], true)
            || str_contains($texto, 'anest');
    }

    /**
     * @return array<int, string>
     */
    private function tokensFila(array $celdas): array
    {
        $tokens = [];

        foreach ($celdas as $celda) {
            $texto = $this->textoCelda($celda);

            if ($texto === '') {
                continue;
            }

            $texto = $this->prepararTexto($texto);
            $texto = preg_replace('/([0-9OoIlSsBbGg])\s+([-·])\s*/u', '$1 $2 ', $texto) ?? $texto;

            foreach (preg_split('/\n+/u', $texto) ?: [] as $linea) {
                $linea = trim($linea);

                if ($linea === '') {
                    continue;
                }

                foreach (preg_split('/\t+|\s{2,}/u', $linea) ?: [] as $parte) {
                    $parte = trim(preg_replace('/\s+/u', ' ', $parte) ?? $parte);

                    if ($parte === '' || preg_match('/^[.\-–—_ ]+$/u', $parte) === 1) {
                        continue;
                    }

                    foreach ($this->dividirParte($parte) as $token) {
                        $token = trim($token);

                        if ($token !== '') {
                            $tokens[] = $token;
                        }
                    }
                }
            }
        }

        return $tokens;
    }

    /**
     * @param  array<string, mixed>  $celda
     */
    private function textoCelda(array $celda): string
    {
        $valor = $celda['valor'] ?? null;
        $formateado = $celda['formateado'] ?? null;

        if (is_numeric($valor)
            && is_string($formateado)
            && trim($formateado) !== ''
            && trim($formateado) !== (string) $valor) {
            $valor = $formateado;
        }

        if ($valor instanceof DateTimeInterface) {
            $valor = $valor->format('d/m/Y');
        } elseif (is_object($valor)) {
            $valor = method_exists($valor, '__toString') ? (string) $valor : (string) $formateado;
        }

        if ($valor === null || (is_string($valor) && trim($valor) === '')) {
            return '';
        }

        if (is_bool($valor)) {
            return $valor ? '1' : '0';
        }

        return trim((string) $valor);
    }

    private function prepararTexto(string $texto): string
    {
        $texto = str_replace(["\r\n", "\r", "\xC2\xA0"], ["\n", "\n", ' '], $texto);
        $lineas = preg_split('/\n+/u', $texto) ?: [];

        while (count($lineas) > 1) {
            $primera = $this->normalizarTexto($lineas[0]);

            if (! str_contains($primera, 'liquidacion')) {
                break;
            }

            array_shift($lineas);
        }

        return implode("\n", $lineas);
    }

    /**
     * @return array<int, string>
     */
    private function dividirParte(string $parte): array
    {
        $parte = trim(preg_replace('/\s+/u', ' ', $parte) ?? $parte);

        if ($parte === '') {
            return [];
        }

        if (preg_match('/^(\d{1,2}\s*[\/.\-]\s*[0-9OoIlSsBbGgZz]{1,2}\s*[\/.\-]\s*\d{2,4})\s+(.+)$/u', $parte, $m) === 1) {
            return array_merge([trim($m[1])], $this->dividirParte($m[2]));
        }

        if (preg_match('/^([0-9OoQIlZSGBgbTq:.,-]{5,12})\s+(\d{1,2}\s*[\/.\-]\s*[0-9OoIlSsBbGgZz]{1,2}\s*[\/.\-]\s*\d{2,4})\s*(.*)$/u', $parte, $m) === 1) {
            $partes = [trim($m[1]), trim($m[2])];

            if (trim($m[3]) !== '') {
                $partes = array_merge($partes, $this->dividirParte($m[3]));
            }

            return $partes;
        }

        if (preg_match('/^([A-Za-zÁÉÍÓÚÜÑáéíóúüñ.]+)\s+(\d{4,7})$/u', $parte, $m) === 1
            && $this->esTipoNom($m[1])) {
            return [trim($m[1]), trim($m[2])];
        }

        if (preg_match('/^(.+?)\s+((?=[0-9OoIlSsBbGg]*[0-9])[0-9OoIlSsBbGg]{1,4}(?:\s*[-·.]\s*){0,3}[A-Za-zÁÉÍÓÚÜÑáéíóúüñ].*)$/u', $parte, $m) === 1
            && mb_strlen(trim($m[1])) >= 2) {
            return array_merge([trim($m[1])], $this->dividirParte($m[2]));
        }

        return [$parte];
    }

    private function corregirDigitosOcr(string $texto): string
    {
        return strtr($texto, self::OCR_DIGITOS);
    }

    /**
     * @return array<string, mixed>
     */
    private function construirAtencion(
        mixed $atencion,
        mixed $fecha,
        mixed $paciente,
        mixed $obraSocial,
        mixed $periodo,
        mixed $codigo,
        mixed $descripcion,
        mixed $importe,
        int $pagina
    ): array {
        return [
            'obra_social' => $this->aTexto($obraSocial),
            'plan' => null,
            'atencion' => $this->aTexto($atencion),
            'fecha' => $this->aTexto($fecha),
            'numero_afiliado' => null,
            'nombre_afiliado' => $this->aTexto($paciente),
            'detalles' => [[
                'fecha' => $this->aTexto($fecha),
                'codigo_nivel' => $this->aTexto($codigo),
                'descripcion_nivel' => $this->aTexto($descripcion),
                'periodo' => $this->aTexto($periodo),
                'porcentaje_cobertura' => null,
                'cantidad' => null,
                'importe' => $importe,
                'pagina' => $pagina,
            ]],
        ];
    }

    private function aTexto(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_scalar($valor)) {
            return trim((string) $valor);
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $celdas
     */
    private function textoFila(array $celdas): string
    {
        return implode(' ', array_filter(
            $this->textosFila($celdas),
            fn ($texto) => $texto !== ''
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $celdas
     * @return array<int, string>
     */
    private function textosFila(array $celdas): array
    {
        $textos = [];

        foreach ($celdas as $celda) {
            $valor = $celda['valor'] ?? ($celda['formateado'] ?? null);
            $textos[] = $valor !== null ? trim((string) $valor) : '';
        }

        return $textos;
    }

    /**
     * @param  array<int, string>  $textos
     */
    private function valorDespuesDe(array $textos, string $etiqueta): ?string
    {
        foreach ($textos as $i => $texto) {
            if (str_contains($this->normalizarTexto($texto), $etiqueta)) {
                for ($j = $i + 1; $j < count($textos); $j++) {
                    if ($textos[$j] !== '') {
                        return $textos[$j];
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $fila
     */
    private function primerTextoDeFila(?array $fila): ?string
    {
        if ($fila === null) {
            return null;
        }

        foreach ($this->textosFila($fila['celdas'] ?? []) as $texto) {
            if ($texto !== '') {
                return $texto;
            }
        }

        return null;
    }

    private function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
        ]);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $texto) ?? $texto);
    }

    /**
     * Convierte un XLSX/XLS en un JSON con TODAS las celdas del rango usado.
     *
     * @return array<string, mixed>
     */
    public function procesar(string $ruta, ?string $nombreOriginal = null): array
    {
        $spreadsheet = IOFactory::load($ruta);
        $hojas = [];
        $totalFilas = 0;
        $totalCeldas = 0;

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $filas = max(0, (int) $sheet->getHighestDataRow());
            $columnas = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
            $datos = [];

            for ($fila = 1; $fila <= $filas; $fila++) {
                $celdas = [];

                for ($columna = 1; $columna <= $columnas; $columna++) {
                    $letra = Coordinate::stringFromColumnIndex($columna);
                    $cell = $sheet->getCell($letra.$fila);
                    $tipo = $cell->getDataType();
                    $formula = null;
                    $valor = $cell->getValue();

                    if ($tipo === DataType::TYPE_FORMULA) {
                        $formula = is_scalar($valor) ? (string) $valor : null;

                        try {
                            $valor = $cell->getCalculatedValue();
                        } catch (Throwable) {
                            // Se conserva la formula si no se puede calcular.
                        }
                    }

                    if ($valor instanceof DateTimeInterface) {
                        $valor = $valor->format('Y-m-d H:i:s');
                    } elseif (is_object($valor)) {
                        $valor = method_exists($valor, '__toString')
                            ? (string) $valor
                            : $cell->getFormattedValue();
                    }

                    $celda = [
                        'columna' => $letra,
                        'valor' => $valor,
                        'tipo' => $tipo,
                        'formateado' => $cell->getFormattedValue(),
                    ];

                    if ($formula !== null) {
                        $celda['formula'] = $formula;
                    }

                    $celdas[] = $celda;
                    $totalCeldas++;
                }

                $datos[] = ['fila' => $fila, 'celdas' => $celdas];
            }

            $totalFilas += $filas;
            $hojas[] = [
                'nombre' => $sheet->getTitle(),
                'filas' => $filas,
                'columnas' => $columnas,
                'datos' => $datos,
            ];
        }

        return [
            'archivo' => [
                'nombre' => $nombreOriginal,
            ],
            'hojas' => $hojas,
            'resumen' => [
                'hojas' => count($hojas),
                'filas' => $totalFilas,
                'celdas' => $totalCeldas,
            ],
        ];
    }

    public function html(string $ruta): string
    {
        $spreadsheet = IOFactory::load($ruta);
        $writer = new Html($spreadsheet);
        $writer->writeAllSheets();

        $temporal = tempnam(sys_get_temp_dir(), 'xlsx_html_');

        try {
            $writer->save($temporal);
            $html = file_get_contents($temporal);
        } finally {
            if (is_string($temporal) && is_file($temporal)) {
                @unlink($temporal);
            }
        }

        return is_string($html) ? $html : '';
    }
}
