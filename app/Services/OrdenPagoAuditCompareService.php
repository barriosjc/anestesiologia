<?php

namespace App\Services;

use App\Models\Cobertura;
use App\Models\DocumentoLlamaparse;
use App\Models\Nivel;
use App\Models\OrdenPagoAuditCab;
use App\Models\Parametro;
use App\Models\ParteCab;
use App\Models\Profesional;
use App\Services\LlamaParse\NumeroOrdenPagoExtractor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Compara el JSON normalizado de una OP / detalle de liquidacion contra los
 * datos cargados en la base (partes_cab, consumos_det, coberturas y niveles).
 */
class OrdenPagoAuditCompareService
{
    /**
     * Nombre del padre de nomenclador que corresponde a IOMA.
     */
    private const NOM_PADRE_IOMA = 2;

    /**
     * Sigla y nombre de las coberturas del sistema -> cod_uom (cache de la
     * corrida).
     *
     * @var array<string, string|null>|null
     */
    private ?array $nombresObraSocial = null;

    /**
     * Codigos de obra social del sistema (cod_uom) para validar que el
     * archivo se pueda relacionar (cache de la corrida).
     *
     * @var array<string, bool>|null
     */
    private ?array $codigosObraSocial = null;

    /**
     * Compara el documento contra la base y genera la auditoria.
     *
     * Con $validarEstricto en true (comportamiento de "Auditar PDF") se
     * cancela todo si algun dato del archivo no tiene equivalencia. Con false
     * ("Ver relaciones") se genera igual la tabla: las filas del archivo que
     * no existen en el sistema quedan como sin_parte / sin_nivel /
     * sin_consumo y las del sistema que no estan en el archivo como
     * sin_archivo.
     *
     * @return array{success: bool, message: string, cabecera_id: int|null, errors: array<int, string>, inconsistencias: array<int, array<string, mixed>>}
     */
    public function auditar(DocumentoLlamaparse $documento, bool $validarEstricto = true): array
    {
        $json = $documento->json;

        if (! is_array($json) || ! is_array($json['atenciones'] ?? null) || $json['atenciones'] === []) {
            return $this->error('El documento no tiene un JSON con atenciones para auditar.');
        }

        if (! in_array($json['documento']['tipo'] ?? null, ['detalle_liquidacion', 'orden_pago'], true)) {
            return $this->error('El documento no es una orden de pago ni un detalle de liquidacion.');
        }

        $cabecera = is_array($json['cabecera'] ?? null) ? $json['cabecera'] : [];
        $resolucionProfesional = $this->resolverProfesional($cabecera, $json, $documento);

        if ($resolucionProfesional['profesional'] === null) {
            return $this->errorProfesional($resolucionProfesional);
        }

        $profesional = $resolucionProfesional['profesional'];

        $nroLiquidacion = $this->texto(
            $cabecera['numero_liquidacion'] ?? $json['documento']['numero'] ?? $cabecera['comprobante'] ?? null
        );
        $nroOp = $this->texto(
            $json['documento']['numero'] ?? $cabecera['numero_liquidacion'] ?? $cabecera['comprobante'] ?? null
        );

        if ($nroOp === null) {
            // Documentos ya procesados antes de leer el numero del markdown:
            // se recupera de ahi en lugar de caer en "DOC-<id>".
            $nroOp = NumeroOrdenPagoExtractor::desdeMarkdown($documento->markdown);
        }

        if ($nroOp === null) {
            $nroOp = 'DOC-'.$documento->id;
        }

        $nroOp = Str::limit($nroOp, 20, '');
        $nroOp = $this->nroOpUnico($nroOp, $documento->id);

        $fecha = $this->fecha($json['documento']['fecha'] ?? null);
        $totalArchivoCab = (float) ($json['documento']['total_informado'] ?? $cabecera['importe'] ?? 0);

        // Retenciones de la hoja "Valores" del XLSX:
        // - IB (Ingresos Brutos): muchas OP no lo tienen (queda en 0).
        // - Caja de Prof. de la salud: si el PDF no la informa se toma 0 y el
        //   documento queda con una advertencia para que el usuario analice
        //   si corresponde solicitarla.
        // - Gastos Administrativos: si el PDF no informa las filas de Gastos
        //   Administrativos se toma 0 (la columna no admite NULL en producción).
        $ib = isset($cabecera['retencion_ib_centavos'])
            ? ((int) $cabecera['retencion_ib_centavos']) / 100
            : 0.0;
        $retencionCaja = isset($cabecera['retencion_caja_centavos'])
            ? ((int) $cabecera['retencion_caja_centavos']) / 100
            : 0.0;
        $retencionGastos = (
            isset($cabecera['retencion_gastos_11_centavos'])
            || isset($cabecera['retencion_gastos_5_centavos'])
        )
            ? (
                (isset($cabecera['retencion_gastos_11_centavos']) ? (int) $cabecera['retencion_gastos_11_centavos'] : 0)
                + (isset($cabecera['retencion_gastos_5_centavos']) ? (int) $cabecera['retencion_gastos_5_centavos'] : 0)
            ) / 100
            : 0.0;

        $mapasNiveles = $this->mapasNiveles();
        $codUomIoma = $this->codUomIoma();
        $nomencladorIoma = $this->mapaNomencladorIoma();
        $codigosObraSocial = $this->codigosObraSocialSistema();
        $coberturasCache = [];
        $partesCache = [];
        $consumosCache = [];

        $inconsistencias = $validarEstricto
            ? $this->detectarInconsistencias(
                $json,
                $profesional,
                $mapasNiveles,
                $codigosObraSocial,
                $codUomIoma,
                $nomencladorIoma,
                $partesCache,
                $coberturasCache
            )
            : [];

        if ($validarEstricto && $inconsistencias !== []) {
            return $this->errorInconsistencias($inconsistencias);
        }

        try {
            $resultado = DB::transaction(function () use (
                $documento,
                $json,
                $profesional,
                $nroOp,
                $nroLiquidacion,
                $fecha,
                $totalArchivoCab,
                $ib,
                $retencionCaja,
                $retencionGastos,
                $mapasNiveles,
                $codUomIoma,
                $nomencladorIoma,
                &$coberturasCache,
                &$partesCache,
                &$consumosCache
            ) {
                $orden = OrdenPagoAuditCab::updateOrCreate(
                    ['documento_llamaparse_id' => $documento->id],
                    [
                        'nro_op'                 => $nroOp,
                        'nro_liquidacion'        => $nroLiquidacion !== null ? Str::limit($nroLiquidacion, 50, '') : null,
                        'profesional_id'         => $profesional->id,
                        'fecha'                  => $fecha?->toDateString(),
                        'op_total'               => $totalArchivoCab,
                        'retencion_caja'         => $retencionCaja,
                        'retencion_gastos'       => $retencionGastos,
                        'op_retenciones'         => $ib,
                        'total_sistema'          => 0,
                        'total_diferencia'       => 0,
                        'cantidad_diferencias'   => 0,
                        'auditado_at'            => now(),
                    ]
                );

                $orden->detalles()->delete();

                $filas = [];
                $partesConDetalle = [];

                // Obra social que informa el archivo para cada parte, para
                // completar las filas que no estan en el archivo y asi no
                // separar la atencion al ordenar.
                $obraSocialPorParte = [];

                foreach ($json['atenciones'] as $atencion) {
                    if (! is_array($atencion)) {
                        continue;
                    }

                    $nroAtencion = $this->texto($atencion['atencion'] ?? null);
                    $fechaAtencion = $this->fecha($atencion['fecha'] ?? null);
                    $obraSocial = $this->texto($atencion['obra_social'] ?? null);
                    $codUomArchivo = $this->resolverCodUomArchivo($obraSocial)['cod_uom'];

                    $parte = $this->buscarParte($nroAtencion, $profesional->id, $partesCache);

                    if ($parte !== null) {
                        $partesConDetalle[$parte->id] = true;

                        if ($codUomArchivo !== null && ! isset($obraSocialPorParte[$parte->id])) {
                            $obraSocialPorParte[$parte->id] = [
                                'obra_social' => $obraSocial,
                                'cod_uom'     => $codUomArchivo,
                            ];
                        }
                    }

                    $cobertura = $parte !== null
                        ? $this->cobertura($parte->cobertura_id, $coberturasCache)
                        : null;
                    $esIoma = $this->esCoberturaIoma($cobertura, $codUomIoma);
                    $consumos = $parte !== null
                        ? $this->consumosPorParte($parte->id, $consumosCache)
                        : ['nivel' => [], 'codigo' => []];
                    $mapaConsumos = $esIoma ? $consumos['codigo'] : $consumos['nivel'];

                    // Grupos de consumos del sistema ya comparados en esta
                    // atencion: si el archivo repite el mismo item, el primero
                    // toma el consumo del sistema y los siguientes quedan sin
                    // contraparte en lugar de sumarse.
                    $gruposUsados = [];

                    foreach ($this->prepararDetalles($atencion['detalles'] ?? []) as $detalle) {
                        $codigo = $this->texto($detalle['codigo_nivel'] ?? null);
                        $descripcionArchivo = $this->texto($detalle['descripcion_nivel'] ?? null);
                        $descripcionBase = $detalle['_descripcion_base'] ?? $descripcionArchivo;
                        $porcentajeArchivo = $detalle['_porcentaje_archivo'] ?? null;
                        $fechaDetalle = $this->fecha($detalle['fecha'] ?? null) ?? $fechaAtencion;
                        $valorArchivo = (float) ($detalle['importe'] ?? 0);
                        $periodoArchivo = $this->texto($detalle['periodo'] ?? null);

                        $resolucion = $this->resolverNivelArchivo(
                            $esIoma,
                            $codigo,
                            $descripcionBase,
                            $descripcionArchivo,
                            $porcentajeArchivo,
                            $mapasNiveles,
                            $nomencladorIoma
                        );

                        $nivel = $resolucion['nivel'];
                        $nomenclador = $resolucion['nomenclador'];
                        $nivelSistema = $resolucion['nivel_sistema'];
                        $claveConsumo = $resolucion['clave_consumo'];
                        $sinNivel = $resolucion['sin_nivel'];

                        $consumo = $claveConsumo !== null
                            ? $this->buscarConsumo($mapaConsumos, $claveConsumo, $porcentajeArchivo)
                            : null;
                        $descripcionSistema = $esIoma
                            ? ($this->texto($nomenclador?->descripcion)
                                ?? $this->texto($nivel?->descripcion)
                                ?? $this->texto($consumo?->descripcion_sistema))
                            : ($this->texto($nivel?->descripcion)
                                ?? $this->texto($consumo?->descripcion_sistema));

                        $fila = [
                            'orden_pago_audit_cab_id' => $orden->id,
                            'parte_cab_id'            => $parte?->id,
                            'consumo_det_id'          => $consumo?->consumo_det_id,
                            'cobertura_id'            => $parte?->cobertura_id,
                            'nivel_id'                => $nivel?->id,
                            'atencion'                => $nroAtencion,
                            'fecha_archivo'           => $fechaDetalle?->toDateString(),
                            'fecha_sistema'           => $parte?->fec_prestacion?->toDateString(),
                            'obra_social_archivo'     => $obraSocial,
                            'cod_uom_archivo'         => $codUomArchivo,
                            'cod_uom_sistema'         => $cobertura?->cod_uom,
                            'periodo_archivo'         => $periodoArchivo,
                            'codigo_archivo'          => $codigo,
                            'nivel_sistema'           => $nivelSistema,
                            'codigo_sistema'          => $consumo?->codigo_sistema,
                            'descripcion_archivo'     => $descripcionArchivo,
                            'descripcion_sistema'     => $descripcionSistema,
                            'valor_archivo'           => round($valorArchivo, 2),
                            'valor_sistema'           => 0,
                            'diferencia'              => round($valorArchivo, 2),
                            'porcentaje_diferencia'   => 100,
                            'porcentaje_archivo'      => $porcentajeArchivo,
                            'porcentaje_sistema'      => $consumo?->porcentaje_sistema,
                            'severidad'               => 'alta',
                            'estado'                  => 'sin_parte',
                            'dif_fecha'               => false,
                            'dif_obra_social'         => false,
                            'dif_descripcion'         => false,
                        ];

                        if ($parte === null) {
                            $filas[] = $fila;

                            continue;
                        }

                        if ($sinNivel) {
                            $fila['estado'] = 'sin_nivel';
                            $fila['severidad'] = 'alta';
                            $filas[] = $fila;

                            continue;
                        }

                        if ($consumo === null) {
                            $fila['estado'] = 'sin_consumo';
                            $fila['severidad'] = 'alta';
                            $filas[] = $fila;

                            continue;
                        }

                        $claveGrupo = $this->claveGrupoConsumo($consumo, $esIoma);

                        // El archivo repite el mismo item en esta atencion: ya
                        // se comparo contra el consumo del sistema, asi que esta
                        // repeticion se muestra sin contraparte.
                        if (isset($gruposUsados[$claveGrupo])) {
                            $fila['consumo_det_id'] = null;
                            $fila['codigo_sistema'] = null;
                            $fila['descripcion_sistema'] = null;
                            $fila['valor_sistema'] = 0;
                            $fila['diferencia'] = round($valorArchivo, 2);
                            $fila['porcentaje_diferencia'] = 100;
                            $fila['porcentaje_sistema'] = null;
                            $fila['estado'] = 'sin_correspondencia';
                            $fila['severidad'] = 'alta';
                            $filas[] = $fila;

                            continue;
                        }

                        $gruposUsados[$claveGrupo] = true;

                        $valorSistema = (float) $consumo->valor_total;
                        $diferencia = round($valorArchivo - $valorSistema, 2);
                        $porcentaje = $this->porcentajeDiferencia($diferencia, $valorSistema);
                        $difValor = abs($diferencia) >= 0.01;

                        $difFecha = $this->fechasDistintas($fechaDetalle, $parte->fec_prestacion);
                        $difObraSocial = ! $this->coincideCodUom($codUomArchivo, $cobertura?->cod_uom);
                        $difDescripcion = $this->textosDistintos($descripcionBase, $descripcionSistema);
                        // El match es por codigo: la descripcion no cambia el
                        // estado. Valor, fecha y obra social si son diferencias.
                        // Los cuatro flags se usan para marcar el dato con un
                        // punto rojo.
                        $hayDiferencia = $difValor || $difFecha || $difObraSocial;

                        $fila['valor_sistema'] = round($valorSistema, 2);
                        $fila['diferencia'] = $diferencia;
                        $fila['porcentaje_diferencia'] = $porcentaje;
                        $fila['dif_fecha'] = $difFecha;
                        $fila['dif_obra_social'] = $difObraSocial;
                        $fila['dif_descripcion'] = $difDescripcion;
                        $fila['estado'] = $hayDiferencia ? 'diferencia' : 'ok';
                        $fila['severidad'] = $this->severidad($difValor, $porcentaje, $hayDiferencia);

                        $filas[] = $fila;
                    }
                }

                foreach (array_keys($partesConDetalle) as $parteId) {
                    $parte = $partesCache[$parteId] ?? ParteCab::find($parteId);

                    if ($parte === null) {
                        continue;
                    }

                    $cobertura = $this->cobertura($parte->cobertura_id, $coberturasCache);
                    $esIoma = $this->esCoberturaIoma($cobertura, $codUomIoma);
                    $consumos = $this->consumosPorParte($parteId, $consumosCache);
                    $mapaConsumos = $esIoma ? $consumos['codigo'] : $consumos['nivel'];
                    $detectados = [];

                    foreach ($filas as $fila) {
                        if ((int) ($fila['parte_cab_id'] ?? 0) !== (int) $parteId) {
                            continue;
                        }

                        if (in_array($fila['estado'], ['sin_consumo', 'sin_nivel', 'sin_parte', 'sin_correspondencia'], true)) {
                            continue;
                        }

                        if ($fila['porcentaje_sistema'] === null) {
                            continue;
                        }

                        $clave = $esIoma
                            ? $this->codigoKey($fila['codigo_archivo'])
                            : $this->codigoKey($fila['nivel_sistema']);

                        if ($clave !== '') {
                            $detectados[$clave.'|'.$this->porcentajeKey($fila['porcentaje_sistema'])] = true;
                        }
                    }

                    foreach ($mapaConsumos as $clave => $porcentajes) {
                        foreach ($porcentajes as $porcentajeKey => $consumo) {
                            if (isset($detectados[$clave.'|'.$porcentajeKey])) {
                                continue;
                            }

                            $valorSistema = (float) $consumo->valor_total;
                            $nivel = $mapasNiveles['nivel']->get(
                                $this->codigoKey($consumo->nivel_sistema)
                            );

                            // La fila no esta en el archivo, pero la atencion si:
                            // se completa con la obra social que el archivo
                            // informo para esa atencion.
                            $obraSocialParte = $obraSocialPorParte[$parte->id] ?? null;

                            $filas[] = [
                                'orden_pago_audit_cab_id' => $orden->id,
                                'parte_cab_id'            => $parte->id,
                                'consumo_det_id'          => $consumo->consumo_det_id,
                                'cobertura_id'            => $parte->cobertura_id,
                                'nivel_id'                => $nivel?->id,
                                'atencion'                => $parte->atencion,
                                'fecha_archivo'           => null,
                                'fecha_sistema'           => $parte->fec_prestacion?->toDateString(),
                                'obra_social_archivo'     => $obraSocialParte['obra_social'] ?? null,
                                'cod_uom_archivo'         => $obraSocialParte['cod_uom'] ?? null,
                                'cod_uom_sistema'         => $cobertura?->cod_uom,
                                'periodo_archivo'         => null,
                                'codigo_archivo'          => null,
                                'nivel_sistema'           => $consumo->nivel_sistema,
                                'codigo_sistema'          => $consumo->codigo_sistema,
                                'descripcion_archivo'     => null,
                                'descripcion_sistema'     => $consumo->descripcion_sistema,
                                'valor_archivo'           => 0,
                                'valor_sistema'           => round($valorSistema, 2),
                                'diferencia'              => round(-1 * $valorSistema, 2),
                                'porcentaje_diferencia'   => 100,
                                'porcentaje_archivo'      => null,
                                'porcentaje_sistema'      => $consumo->porcentaje_sistema,
                                'severidad'               => 'alta',
                                'estado'                  => 'sin_archivo',
                                'dif_fecha'               => false,
                                'dif_obra_social'         => false,
                                'dif_descripcion'         => false,
                            ];
                        }
                    }
                }

                $totalArchivo = round(array_sum(array_column($filas, 'valor_archivo')), 2);
                $totalSistema = round(array_sum(array_column($filas, 'valor_sistema')), 2);
                $totalArchivo = $totalArchivoCab > 0 ? round($totalArchivoCab, 2) : $totalArchivo;
                $cantidadDiferencias = count(array_filter(
                    $filas,
                    fn (array $fila) => $fila['estado'] !== 'ok'
                ));

                $now = now();
                $filas = array_map(function (array $fila) use ($now) {
                    $fila['created_at'] = $now;
                    $fila['updated_at'] = $now;

                    return $fila;
                }, $filas);

                foreach (array_chunk($filas, 500) as $chunk) {
                    DB::table('ordenes_pagos_audit_det')->insert($chunk);
                }

                $orden->update([
                    'op_total'             => $totalArchivo,
                    'total_sistema'        => $totalSistema,
                    'total_diferencia'     => round($totalArchivo - $totalSistema, 2),
                    'cantidad_diferencias' => $cantidadDiferencias,
                    'auditado_at'          => $now,
                ]);

                return $orden;
            });
        } catch (Throwable $e) {
            report($e);

            return $this->error('No se pudo generar la auditoria: '.$e->getMessage());
        }

        return [
            'success'         => true,
            'message'         => 'Auditoria generada correctamente.',
            'cabecera_id'     => $resultado->id,
            'errors'          => [],
            'inconsistencias' => [],
        ];
    }

    /**
     * @return array{success: bool, message: string, cabecera_id: null, errors: array<int, string>, inconsistencias: array<int, array<string, mixed>>}
     */
    private function error(string $mensaje): array
    {
        return [
            'success'         => false,
            'message'         => $mensaje,
            'cabecera_id'     => null,
            'errors'          => [$mensaje],
            'inconsistencias' => [],
        ];
    }

    /**
     * Busca el profesional comparando el CUIT sin guiones (u otros
     * separadores) contra la base y, si no aparece, por nombre normalizado.
     * Si el JSON normalizado no trae CUIT, lo busca en el texto ya extraido
     * del documento (items/markdown de LlamaParse).
     *
     * @param  array<string, mixed>  $cabecera
     * @param  array<string, mixed>  $json
     * @return array{profesional: Profesional|null, cuit_archivo: string|null, cuit_normalizado: string|null, nombre_archivo: string|null, cuits_documento: array<int, string>, candidatos: Collection<int, Profesional>}
     */
    private function resolverProfesional(array $cabecera, array $json, ?DocumentoLlamaparse $documento = null): array
    {
        $cuitArchivo = $this->texto($cabecera['cuit'] ?? $json['documento']['cuit'] ?? null);
        $cuitNormalizado = $this->soloDigitos($cuitArchivo);
        $nombreArchivo = $this->texto(
            $cabecera['profesional'] ?? $json['documento']['profesional'] ?? null
        );

        if ($this->esEtiquetaDeEncabezado($nombreArchivo)) {
            $nombreArchivo = null;
        }

        $profesional = null;
        $cuitsDocumento = [];

        if ($cuitNormalizado !== null) {
            $profesional = $this->buscarProfesionalPorCuit($cuitNormalizado);
        }

        if ($profesional === null && $documento !== null) {
            $cuitsDocumento = $this->cuitsEnDocumento($documento);

            foreach ($cuitsDocumento as $cuit) {
                if ($cuit === $cuitNormalizado) {
                    continue;
                }

                $encontrado = $this->buscarProfesionalPorCuit($cuit);

                if ($encontrado !== null) {
                    $profesional = $encontrado;
                    $cuitArchivo = $this->formatearCuit($cuit);
                    $cuitNormalizado = $cuit;
                    break;
                }
            }
        }

        if ($profesional === null && $nombreArchivo !== null) {
            $profesional = $this->buscarProfesionalPorNombre($nombreArchivo);
        }

        return [
            'profesional'      => $profesional,
            'cuit_archivo'     => $cuitArchivo,
            'cuit_normalizado' => $cuitNormalizado,
            'nombre_archivo'   => $nombreArchivo,
            'cuits_documento'  => $cuitsDocumento,
            'candidatos'       => $profesional === null
                ? $this->candidatosProfesional($cuitNormalizado, $nombreArchivo)
                : collect(),
        ];
    }

    /**
     * CUITs (normalizados a digitos) presentes en el texto que LlamaParse ya
     * extrajo, para documentos cuyo JSON no los incluye en la cabecera.
     *
     * @return array<int, string>
     */
    private function cuitsEnDocumento(DocumentoLlamaparse $documento): array
    {
        $textos = [];
        $this->acumularTextosCuit($documento->items, $textos);
        $this->acumularTextosCuit($documento->markdown, $textos);

        $encontrados = [];

        foreach ($textos as $texto) {
            $coincidencias = preg_match_all(
                '/(?<!\d)(\d{2})\s*[-.\s]?\s*(\d{8})\s*[-.\s]?\s*(\d)(?!\d)/u',
                $texto,
                $m,
                PREG_SET_ORDER
            );

            if ($coincidencias === false) {
                continue;
            }

            foreach ($m as $coincidencia) {
                $encontrados[$coincidencia[1].$coincidencia[2].$coincidencia[3]] = true;
            }
        }

        return array_keys($encontrados);
    }

    /**
     * @param  mixed  $valor
     * @param  array<int, string>  $textos
     */
    private function acumularTextosCuit(mixed $valor, array &$textos): void
    {
        if (is_string($valor)) {
            if (str_contains($valor, 'CUIT') || preg_match('/\d{2}\s*-\s*\d{8}\s*-\s*\d/', $valor) === 1) {
                $textos[] = $valor;
            }

            return;
        }

        if (is_array($valor) || is_object($valor)) {
            foreach ((array) $valor as $item) {
                $this->acumularTextosCuit($item, $textos);
            }
        }
    }

    private function formatearCuit(string $cuit): string
    {
        if (preg_match('/^(\d{2})(\d{8})(\d)$/', $cuit, $m) !== 1) {
            return $cuit;
        }

        return $m[1].'-'.$m[2].'-'.$m[3];
    }

    /**
     * Compara el CUIT del archivo (ya sin guiones) contra profesionales.cuit,
     * quitando tambien los separadores que pueda tener la base.
     */
    private function buscarProfesionalPorCuit(string $cuit): ?Profesional
    {
        $profesional = Profesional::query()
            ->whereRaw(
                "REPLACE(REPLACE(REPLACE(REPLACE(cuit, '-', ''), '.', ''), ' ', ''), '/', '') = ?",
                [$cuit]
            )
            ->first();

        if ($profesional !== null) {
            return $profesional;
        }

        $sufijo = substr($cuit, -6);

        if ($sufijo === '') {
            return null;
        }

        return Profesional::query()
            ->whereNotNull('cuit')
            ->where('cuit', 'like', '%'.$sufijo.'%')
            ->get(['id', 'nombre', 'cuit'])
            ->first(fn (Profesional $candidato) => $this->soloDigitos($candidato->cuit) === $cuit);
    }

    /**
     * Compara el nombre del archivo ignorando mayusculas, acentos, comas y
     * puntos. Si no hay coincidencia exacta prueba que esten todos los
     * apellidos/nombres del archivo dentro del nombre de la base.
     */
    private function buscarProfesionalPorNombre(string $nombre): ?Profesional
    {
        $normalizado = $this->normalizarNombre($nombre);
        $tokens = $this->tokensNombre($normalizado);

        $profesional = Profesional::whereRaw('UPPER(nombre) = ?', [mb_strtoupper($nombre)])->first();

        if ($profesional !== null) {
            return $profesional;
        }

        if ($tokens === []) {
            return Profesional::where('nombre', 'like', '%'.$nombre.'%')
                ->orderBy('nombre')
                ->first();
        }

        $candidatos = Profesional::query()
            ->where(function ($query) use ($tokens) {
                foreach ($tokens as $token) {
                    $query->orWhere('nombre', 'like', '%'.$token.'%');
                }
            })
            ->orderBy('nombre')
            ->limit(50)
            ->get(['id', 'nombre', 'cuit']);

        $profesional = $this->coincidenciaPorTokens($candidatos, $normalizado, $tokens);

        if ($profesional !== null) {
            return $profesional;
        }

        // Barrido completo por si la base guarda el nombre con acentos u otro
        // formato que el LIKE no puede encontrar.
        $profesional = $this->coincidenciaPorTokens(
            Profesional::query()->orderBy('id')->cursor(),
            $normalizado,
            $tokens
        );

        if ($profesional !== null) {
            return $profesional;
        }

        return Profesional::where('nombre', 'like', '%'.$nombre.'%')
            ->orderBy('nombre')
            ->first();
    }

    /**
     * Busca la mejor coincidencia entre los candidatos comparando el nombre
     * normalizado y todas las palabras significativas del nombre del archivo.
     *
     * @param  iterable<int, Profesional>  $candidatos
     * @param  array<int, string>  $tokens
     */
    private function coincidenciaPorTokens(iterable $candidatos, string $normalizado, array $tokens): ?Profesional
    {
        if ($tokens === []) {
            return null;
        }

        $mejor = null;
        $mejorExtra = null;

        foreach ($candidatos as $candidato) {
            $candidatoNormalizado = $this->normalizarNombre($candidato->nombre);

            if ($candidatoNormalizado === $normalizado) {
                return $candidato;
            }

            $candidatoTokens = $this->tokensNombre($candidatoNormalizado);

            if (array_diff($tokens, $candidatoTokens) !== []) {
                continue;
            }

            $extra = count(array_diff($candidatoTokens, $tokens));

            if ($mejorExtra === null || $extra < $mejorExtra) {
                $mejor = $candidato;
                $mejorExtra = $extra;
            }
        }

        return $mejor;
    }

    /**
     * Profesionales de la base que podrian corresponder al CUIT o al nombre
     * del archivo, para mostrarlos junto al error.
     *
     * @return Collection<int, Profesional>
     */
    private function candidatosProfesional(?string $cuit, ?string $nombre): Collection
    {
        $candidatos = collect();

        if ($cuit !== null) {
            $sufijo = substr($cuit, -6);

            if ($sufijo !== '') {
                $candidatos = Profesional::query()
                    ->whereNotNull('cuit')
                    ->where('cuit', 'like', '%'.$sufijo.'%')
                    ->orderBy('nombre')
                    ->get(['id', 'nombre', 'cuit'])
                    ->filter(fn (Profesional $candidato) => $this->soloDigitos($candidato->cuit) === $cuit)
                    ->values();
            }
        }

        if ($nombre !== null) {
            $tokens = $this->tokensNombre($this->normalizarNombre($nombre));

            if ($tokens !== []) {
                $porNombre = Profesional::query()
                    ->where(function ($query) use ($tokens) {
                        foreach ($tokens as $token) {
                            $query->orWhere('nombre', 'like', '%'.$token.'%');
                        }
                    })
                    ->orderBy('nombre')
                    ->limit(5)
                    ->get(['id', 'nombre', 'cuit']);

                $candidatos = $candidatos->concat($porNombre);
            }
        }

        if ($candidatos->isEmpty() && $nombre !== null) {
            $tokens = $this->tokensNombre($this->normalizarNombre($nombre));

            if ($tokens !== []) {
                $candidatos = Profesional::query()
                    ->orderBy('nombre')
                    ->get(['id', 'nombre', 'cuit'])
                    ->filter(
                        fn (Profesional $candidato) => array_diff(
                            $tokens,
                            $this->tokensNombre($this->normalizarNombre($candidato->nombre))
                        ) === []
                    )
                    ->values();
            }
        }

        return $candidatos
            ->unique(fn (Profesional $candidato) => $candidato->id)
            ->take(5)
            ->values();
    }

    /**
     * Devuelve un error detallado indicando que dato del PDF no se pudo
     * relacionar con la base, con los valores leidos y las coincidencias
     * aproximadas encontradas.
     *
     * @param  array{profesional: Profesional|null, cuit_archivo: string|null, cuit_normalizado: string|null, nombre_archivo: string|null, cuits_documento: array<int, string>, candidatos: Collection<int, Profesional>}  $resolucion
     * @return array{success: bool, message: string, cabecera_id: null, errors: array<int, string>, inconsistencias: array<int, array<string, mixed>>}
     */
    private function errorProfesional(array $resolucion): array
    {
        $cuitArchivo = $resolucion['cuit_archivo'];
        $cuitNormalizado = $resolucion['cuit_normalizado'];
        $nombreArchivo = $resolucion['nombre_archivo'];
        $cuitsDocumento = $resolucion['cuits_documento'] ?? [];
        $candidatos = $resolucion['candidatos'] ?? collect();

        $errors = [];

        if ($cuitArchivo === null) {
            if ($cuitsDocumento !== []) {
                $encontrados = implode(', ', array_map(
                    fn (string $cuit) => $this->formatearCuit($cuit),
                    $cuitsDocumento
                ));

                $errors[] = 'CUIT: el JSON normalizado no lo informa, pero en el PDF se encontraron: '
                    .$encontrados.'; ninguno coincide con profesionales.cuit.';
            } else {
                $errors[] = 'CUIT: el PDF no informa el CUIT del profesional.';
            }
        } elseif ($cuitNormalizado === null) {
            $errors[] = 'CUIT: el PDF informa «'.$cuitArchivo.'», pero no contiene números para comparar.';
        } else {
            $normalizado = $cuitNormalizado !== $cuitArchivo
                ? ' (sin guiones: '.$cuitNormalizado.')'
                : '';

            $errors[] = 'CUIT: «'.$cuitArchivo.'»'.$normalizado
                .' no coincide con ningún registro de profesionales.cuit.';
        }

        if ($nombreArchivo === null) {
            $errors[] = 'Nombre: el PDF no informa el nombre del profesional.';
        } else {
            $errors[] = 'Nombre: «'.$nombreArchivo.'» no coincide con ningún registro de profesionales.nombre.';
        }

        if ($candidatos->isNotEmpty()) {
            $detalle = $candidatos->map(function (Profesional $candidato) {
                $cuit = $this->texto($candidato->cuit) ?? 'sin CUIT';

                return '#'.$candidato->id.' '.$candidato->nombre.' (CUIT '.$cuit.')';
            })->implode('; ');

            $errors[] = 'Posibles coincidencias en la base: '.$detalle.'.';
        }

        $mensaje = 'No se pudo identificar el profesional por CUIT ni por nombre.';

        if ($cuitArchivo !== null) {
            $mensaje .= ' CUIT del PDF: '.$cuitArchivo.'.';
        } elseif ($cuitsDocumento !== []) {
            $mensaje .= ' CUIT(s) del PDF: '.implode(', ', array_map(
                fn (string $cuit) => $this->formatearCuit($cuit),
                $cuitsDocumento
            )).'.';
        } else {
            $mensaje .= ' El PDF no informa CUIT.';
        }

        if ($nombreArchivo !== null) {
            $mensaje .= ' Nombre del PDF: '.$nombreArchivo.'.';
        } else {
            $mensaje .= ' El PDF no informa nombre.';
        }

        return [
            'success'         => false,
            'message'         => $mensaje,
            'cabecera_id'     => null,
            'errors'          => $errors,
            'inconsistencias' => [],
        ];
    }

    /**
     * @param  array<int, ParteCab>  $cache
     */
    private function buscarParte(?string $atencion, int $profesionalId, array &$cache): ?ParteCab
    {
        if ($atencion === null || $atencion === '') {
            return null;
        }

        $clave = $profesionalId.'|'.$atencion;

        if (array_key_exists($clave, $cache)) {
            return $cache[$clave];
        }

        $query = ParteCab::query()->where('profesional_id', $profesionalId);

        $parte = (clone $query)->where('atencion', $atencion)
            ->orderByDesc('fec_prestacion')
            ->orderByDesc('id')
            ->first();

        if ($parte === null && ctype_digit($atencion)) {
            $sinCeros = ltrim($atencion, '0');
            $sinCeros = $sinCeros === '' ? '0' : $sinCeros;

            if ($sinCeros !== $atencion) {
                $parte = (clone $query)->where('atencion', $sinCeros)
                    ->orderByDesc('fec_prestacion')
                    ->orderByDesc('id')
                    ->first();
            }
        }

        if ($parte === null && ctype_digit($atencion)) {
            // SIGNED y no INTEGER: "AS INTEGER" es sintaxis de SQLite y MySQL
            // lo rechaza. SQLite lo interpreta como afinidad NUMERIC.
            $parte = (clone $query)
                ->whereRaw('CAST(atencion AS SIGNED) = ?', [(int) $atencion])
                ->orderByDesc('fec_prestacion')
                ->orderByDesc('id')
                ->first();
        }

        return $cache[$clave] = $parte;
    }

    /**
     * Prepara los detalles del archivo uno por uno.
     *
     * No agrupa ni suma importes: cada item del archivo se muestra como una
     * fila propia aunque se repita dentro de la misma atencion. El primer item
     * de cada grupo se compara contra el consumo del sistema; las repeticiones
     * quedan con el estado `sin_correspondencia`.
     *
     * @param  array<int, array<string, mixed>>  $detalles
     * @return array<int, array<string, mixed>>
     */
    private function prepararDetalles(array $detalles): array
    {
        $preparados = [];

        foreach ($detalles as $detalle) {
            if (! is_array($detalle)) {
                continue;
            }

            [$descripcionBase, $porcentaje] = $this->separarPorcentajeDescripcion(
                $this->texto($detalle['descripcion_nivel'] ?? null)
            );

            $detalle['importe'] = (float) ($detalle['importe'] ?? 0);
            $detalle['_descripcion_base'] = $descripcionBase;
            $detalle['_porcentaje_archivo'] = $porcentaje;

            $preparados[] = $detalle;
        }

        return $preparados;
    }

    /**
     * Clave del grupo de consumos del sistema (nivel o codigo mas porcentaje)
     * contra el que se comparo un detalle del archivo.
     */
    private function claveGrupoConsumo(object $consumo, bool $esIoma): string
    {
        $clave = $esIoma
            ? $this->codigoKey($consumo->codigo_sistema)
            : $this->codigoKey($consumo->nivel_sistema);

        return $clave.'|'.$this->porcentajeKey($consumo->porcentaje_sistema);
    }

    /**
     * Consumos de un parte agrupados por nivel y porcentaje (para las
     * coberturas comunes) y por codigo y porcentaje (para IOMA).
     *
     * @param  array<int, array{nivel: array<string, array<string, object>>, codigo: array<string, array<string, object>>}>  $cache
     * @return array{nivel: array<string, array<string, object>>, codigo: array<string, array<string, object>>}
     */
    private function consumosPorParte(int $parteId, array &$cache): array
    {
        if (isset($cache[$parteId])) {
            return $cache[$parteId];
        }

        $niveles = DB::table('nomenclador')
            ->selectRaw('id, nom_padre_id, nivel, descripcion, codigo')
            ->unionAll(
                DB::table('nom_practicas_estudios')
                    ->selectRaw('id, nom_padre_id, codigo as nivel, nombre as descripcion, codigo')
            );

        $filas = DB::table('consumos_det as cd')
            ->join('consumos_cab as cc', 'cc.id', '=', 'cd.consumo_cab_id')
            ->joinSub($niveles, 'nv', function ($join) {
                $join->on('cd.nomenclador_id', '=', 'nv.id')
                    ->on('cd.nom_padre_id', '=', 'nv.nom_padre_id');
            })
            ->where('cc.parte_cab_id', $parteId)
            ->whereNull('cc.deleted_at')
            ->whereNull('cd.deleted_at')
            ->selectRaw('nv.nivel as nivel_sistema')
            ->selectRaw('nv.descripcion as descripcion_sistema')
            ->selectRaw('nv.codigo as codigo_sistema')
            ->selectRaw('cd.porcentaje as porcentaje_sistema')
            ->selectRaw('SUM(cd.valor) as valor_total')
            ->selectRaw('COUNT(cd.id) as cantidad')
            ->selectRaw('MIN(cd.id) as consumo_det_id')
            ->groupBy('nivel_sistema', 'descripcion_sistema', 'codigo_sistema', 'porcentaje_sistema')
            ->get();

        $porNivel = [];
        $porCodigo = [];

        foreach ($filas as $fila) {
            $nivelKey = $this->codigoKey($fila->nivel_sistema);
            $porcentajeKey = $this->porcentajeKey($fila->porcentaje_sistema);
            $codigoKey = $this->codigoKey($fila->codigo_sistema);

            if ($codigoKey !== '') {
                $porCodigo[$codigoKey][$porcentajeKey] = (object) [
                    'nivel_sistema'       => (string) $fila->nivel_sistema,
                    'descripcion_sistema' => $fila->descripcion_sistema,
                    'codigo_sistema'      => $fila->codigo_sistema,
                    'porcentaje_sistema'  => (float) $fila->porcentaje_sistema,
                    'valor_total'         => (float) $fila->valor_total,
                    'cantidad'            => (int) $fila->cantidad,
                    'consumo_det_id'      => $fila->consumo_det_id,
                ];
            }

            if ($nivelKey === '') {
                continue;
            }

            $actual = $porNivel[$nivelKey][$porcentajeKey] ?? null;

            if ($actual === null) {
                $porNivel[$nivelKey][$porcentajeKey] = (object) [
                    'nivel_sistema'       => (string) $fila->nivel_sistema,
                    'descripcion_sistema' => $fila->descripcion_sistema,
                    'codigo_sistema'      => null,
                    'porcentaje_sistema'  => (float) $fila->porcentaje_sistema,
                    'valor_total'         => (float) $fila->valor_total,
                    'cantidad'            => (int) $fila->cantidad,
                    'consumo_det_id'      => $fila->consumo_det_id,
                ];

                continue;
            }

            $actual->valor_total += (float) $fila->valor_total;
            $actual->cantidad += (int) $fila->cantidad;

            if ((int) $fila->consumo_det_id < (int) $actual->consumo_det_id) {
                $actual->consumo_det_id = $fila->consumo_det_id;
            }
        }

        return $cache[$parteId] = ['nivel' => $porNivel, 'codigo' => $porCodigo];
    }

    /**
     * Busca el consumo que corresponde a una clave (nivel o codigo) y al
     * porcentaje detectado en la descripcion del archivo. Si el archivo no
     * informa porcentaje se prioriza el 100% y, si no hay, la unica fila.
     *
     * @param  array<string, array<string, object>>  $mapa
     */
    private function buscarConsumo(array $mapa, string $clave, ?float $porcentaje): ?object
    {
        if (! isset($mapa[$clave])) {
            return null;
        }

        $porcentajes = $mapa[$clave];

        if ($porcentaje !== null) {
            return $porcentajes[$this->porcentajeKey($porcentaje)] ?? null;
        }

        if (isset($porcentajes['100'])) {
            return $porcentajes['100'];
        }

        if (count($porcentajes) !== 1) {
            return null;
        }

        $unico = reset($porcentajes);

        return $unico === false ? null : $unico;
    }

    /**
     * Resuelve el nivel de una fila del archivo segun la cobertura.
     *
     * Para IOMA se usa `nomenclador` (nom_padre_id = 2) por codigo. Para el
     * resto, si la descripcion informa porcentaje se busca por descripcion y
     * no se usa el codigo; si no informa porcentaje se usa `niveles.cod_uom`.
     *
     * @param  array{cod_uom: Collection<string, Nivel>, nivel: Collection<string, Nivel>, descripcion: Collection<string, Nivel>}  $mapasNiveles
     * @param  Collection<string, object>  $nomencladorIoma
     * @return array{nivel: Nivel|null, nomenclador: object|null, nivel_sistema: string|null, clave_consumo: string|null, sin_nivel: bool}
     */
    private function resolverNivelArchivo(
        bool $esIoma,
        ?string $codigo,
        ?string $descripcionBase,
        ?string $descripcionArchivo,
        ?float $porcentaje,
        array $mapasNiveles,
        Collection $nomencladorIoma
    ): array {
        $nivel = null;
        $nomenclador = null;
        $nivelSistema = null;
        $claveConsumo = null;

        if ($esIoma) {
            $nomenclador = $codigo !== null
                ? $nomencladorIoma->get($this->codigoKey($codigo))
                : null;
            $nivelSistema = $this->texto($nomenclador?->nivel);
            $nivel = $nivelSistema !== null
                ? $mapasNiveles['nivel']->get($this->codigoKey($nivelSistema))
                : null;
            $claveConsumo = $codigo !== null ? $this->codigoKey($codigo) : null;
        } else {
            if ($porcentaje !== null) {
                $nivel = $this->buscarNivelPorDescripcion(
                    $mapasNiveles['descripcion'],
                    $descripcionBase,
                    $descripcionArchivo
                );
            } elseif ($codigo !== null) {
                $nivel = $mapasNiveles['cod_uom']->get($this->codigoKey($codigo));
            }

            $nivelSistema = $nivel?->nivel;
            $claveConsumo = $nivelSistema !== null
                ? $this->codigoKey($nivelSistema)
                : null;
        }

        return [
            'nivel'         => $nivel,
            'nomenclador'   => $nomenclador,
            'nivel_sistema' => $nivelSistema,
            'clave_consumo' => $claveConsumo,
            'sin_nivel'     => $esIoma ? $nomenclador === null : $nivel === null,
        ];
    }

    /**
     * Busca el nivel por descripcion cuando el archivo informa un porcentaje.
     *
     * El archivo puede traer texto extra alrededor del porcentaje
     * ("Especial D 30% valor base", "Especial D valor base 30%"). Primero se
     * prueba la descripcion exacta y, si no coincide, se busca la descripcion
     * de `niveles` mas larga que sea prefijo del texto del archivo.
     *
     * @param  Collection<string, Nivel>  $porDescripcion
     */
    private function buscarNivelPorDescripcion(
        Collection $porDescripcion,
        ?string $descripcionBase,
        ?string $descripcionArchivo
    ): ?Nivel {
        $baseKey = $this->descripcionKey($descripcionBase);

        if ($baseKey !== '' && $porDescripcion->has($baseKey)) {
            return $porDescripcion->get($baseKey);
        }

        $archivoKey = $this->descripcionKey($descripcionArchivo);

        if ($archivoKey !== '' && $porDescripcion->has($archivoKey)) {
            return $porDescripcion->get($archivoKey);
        }

        if ($archivoKey === '') {
            return null;
        }

        $mejor = null;
        $mejorLargo = 0;

        foreach ($porDescripcion as $clave => $nivel) {
            $clave = (string) $clave;

            if ($clave === '' || strlen($clave) <= $mejorLargo) {
                continue;
            }

            if (! str_starts_with($archivoKey, $clave)) {
                continue;
            }

            // La descripcion encontrada tiene que terminar en un limite de
            // palabra: asi "Especial D" no se confunde con "Especial DX".
            if (strlen($archivoKey) > strlen($clave)) {
                $siguiente = substr($archivoKey, strlen($clave), 1);

                if ($siguiente !== '' && ctype_alnum($siguiente)) {
                    continue;
                }
            }

            $mejor = $nivel;
            $mejorLargo = strlen($clave);
        }

        return $mejor;
    }

    /**
     * Recorre el archivo antes de guardar y devuelve los datos que no se
     * pueden relacionar (atencion inexistente, obra social sin codigo en el
     * sistema o codigos/descripciones sin equivalencia). Si hay alguno, la
     * auditoria se cancela para que se codifiquen primero.
     *
     * @param  array<string, mixed>  $json
     * @param  array{cod_uom: Collection<string, Nivel>, nivel: Collection<string, Nivel>, descripcion: Collection<string, Nivel>}  $mapasNiveles
     * @param  array<string, bool>  $codigosObraSocial
     * @param  Collection<string, object>  $nomencladorIoma
     * @param  array<string, ParteCab>  $partesCache
     * @param  array<int, Cobertura|null>  $coberturasCache
     * @return array<int, array{atencion: string|null, obra_social: string|null, codigo: string|null, descripcion: string|null, porcentaje: float|null, motivo: string, tipo: string}>
     */
    private function detectarInconsistencias(
        array $json,
        Profesional $profesional,
        array $mapasNiveles,
        array $codigosObraSocial,
        ?string $codUomIoma,
        Collection $nomencladorIoma,
        array &$partesCache,
        array &$coberturasCache
    ): array {
        $inconsistencias = [];

        foreach ($json['atenciones'] as $atencion) {
            if (! is_array($atencion)) {
                continue;
            }

            $nroAtencion = $this->texto($atencion['atencion'] ?? null);
            $obraSocial = $this->texto($atencion['obra_social'] ?? null);
            $parte = $this->buscarParte($nroAtencion, $profesional->id, $partesCache);
            $resolucionObraSocial = $this->resolverCodUomArchivo($obraSocial);
            $codUomArchivo = $resolucionObraSocial['cod_uom'];

            if ($codUomArchivo === null) {
                $inconsistencias['obra_social|'.($nroAtencion ?? '').'|sin_codigo'] = [
                    'atencion'    => $nroAtencion,
                    'obra_social' => $obraSocial,
                    'codigo'      => null,
                    'descripcion' => null,
                    'porcentaje'  => null,
                    'motivo'      => $resolucionObraSocial['motivo'],
                    'tipo'        => 'obra_social',
                ];
            } elseif (! isset($codigosObraSocial[$codUomArchivo])) {
                $inconsistencias['obra_social|'.($nroAtencion ?? '').'|'.$codUomArchivo] = [
                    'atencion'    => $nroAtencion,
                    'obra_social' => $obraSocial,
                    'codigo'      => $codUomArchivo,
                    'descripcion' => null,
                    'porcentaje'  => null,
                    'motivo'      => 'La obra social del archivo ('.$codUomArchivo.') no coincide con coberturas.cod_uom.',
                    'tipo'        => 'obra_social',
                ];
            }

            if ($parte === null) {
                $inconsistencias['atencion|'.($nroAtencion ?? '').'|'.($obraSocial ?? '')] = [
                    'atencion'    => $nroAtencion,
                    'obra_social' => $obraSocial,
                    'codigo'      => null,
                    'descripcion' => null,
                    'porcentaje'  => null,
                    'motivo'      => 'La atención no existe en partes_cab para el profesional.',
                    'tipo'        => 'atencion',
                ];

                continue;
            }

            $cobertura = $this->cobertura($parte->cobertura_id, $coberturasCache);
            $esIoma = $this->esCoberturaIoma($cobertura, $codUomIoma);

            foreach ($this->prepararDetalles($atencion['detalles'] ?? []) as $detalle) {
                $codigo = $this->texto($detalle['codigo_nivel'] ?? null);
                $descripcionArchivo = $this->texto($detalle['descripcion_nivel'] ?? null);
                $descripcionBase = $detalle['_descripcion_base'] ?? $descripcionArchivo;
                $porcentaje = $detalle['_porcentaje_archivo'] ?? null;
                $porcentaje = $porcentaje !== null ? (float) $porcentaje : null;

                $resolucion = $this->resolverNivelArchivo(
                    $esIoma,
                    $codigo,
                    $descripcionBase,
                    $descripcionArchivo,
                    $porcentaje,
                    $mapasNiveles,
                    $nomencladorIoma
                );

                if (! $resolucion['sin_nivel']) {
                    continue;
                }

                $tipo = $esIoma ? 'ioma' : ($porcentaje !== null ? 'descripcion' : 'codigo');

                $motivo = $esIoma
                    ? ($codigo === null
                        ? 'El archivo no informa un código de IOMA para la práctica.'
                        : 'Código sin equivalencia en el nomenclador de IOMA (nom_padre_id = 2).')
                    : ($porcentaje !== null
                        ? 'La descripción con porcentaje no tiene un nivel asociado en niveles.descripcion.'
                        : ($codigo === null
                            ? 'El archivo no informa código y la descripción no tiene equivalencia.'
                            : 'Código sin equivalencia en niveles.cod_uom.'));

                $clave = implode('|', [
                    $tipo,
                    $nroAtencion ?? '',
                    $codigo ?? '',
                    $descripcionArchivo ?? '',
                    $porcentaje !== null ? $this->porcentajeKey($porcentaje) : '',
                ]);

                if (isset($inconsistencias[$clave])) {
                    continue;
                }

                $inconsistencias[$clave] = [
                    'atencion'    => $nroAtencion,
                    'obra_social' => $obraSocial,
                    'codigo'      => $codigo,
                    'descripcion' => $descripcionArchivo,
                    'porcentaje'  => $porcentaje,
                    'motivo'      => $motivo,
                    'tipo'        => $tipo,
                ];
            }
        }

        return array_values($inconsistencias);
    }

    /**
     * @param  array<int, array{atencion: string|null, obra_social: string|null, codigo: string|null, descripcion: string|null, porcentaje: float|null, motivo: string, tipo: string}>  $inconsistencias
     * @return array{success: bool, message: string, cabecera_id: null, errors: array<int, string>, inconsistencias: array<int, array<string, mixed>>}
     */
    private function errorInconsistencias(array $inconsistencias): array
    {
        $cantidad = count($inconsistencias);

        $mensaje = $cantidad === 1
            ? 'Se canceló la auditoría: hay 1 dato del archivo sin código para relacionar.'
            : 'Se canceló la auditoría: hay '.$cantidad.' datos del archivo sin código para relacionar.';

        $mensaje .= ' Codificalos y volvé a intentar.';

        $errors = array_map(function (array $item): string {
            $partes = [];

            if ($item['atencion']) {
                $partes[] = 'Atención '.$item['atencion'];
            }

            $partes[] = $item['codigo'] ? 'código '.$item['codigo'] : 'sin código';

            if ($item['descripcion']) {
                $partes[] = '"'.$item['descripcion'].'"';
            }

            return implode(' · ', $partes).': '.$item['motivo'];
        }, $inconsistencias);

        return [
            'success'         => false,
            'message'         => $mensaje,
            'cabecera_id'     => null,
            'errors'          => $errors,
            'inconsistencias' => $inconsistencias,
        ];
    }

    /**
     * @param  array<int, Cobertura|null>  $cache
     */
    private function cobertura(?int $coberturaId, array &$cache): ?Cobertura
    {
        if ($coberturaId === null) {
            return null;
        }

        if (! array_key_exists($coberturaId, $cache)) {
            $cache[$coberturaId] = Cobertura::find($coberturaId);
        }

        return $cache[$coberturaId];
    }

    /**
     * @return array{
     *     cod_uom: Collection<string, Nivel>,
     *     nivel: Collection<string, Nivel>,
     *     descripcion: Collection<string, Nivel>
     * }
     */
    private function mapasNiveles(): array
    {
        $porCodUom = collect();
        $porNivel = collect();
        $porDescripcion = collect();

        foreach (Nivel::query()->orderBy('id')->get() as $nivel) {
            $codUom = $this->codigoKey($nivel->cod_uom);

            if ($codUom !== '' && ! $porCodUom->has($codUom)) {
                $porCodUom->put($codUom, $nivel);
            }

            $nivelKey = $this->codigoKey($nivel->nivel);

            if ($nivelKey !== '' && ! $porNivel->has($nivelKey)) {
                $porNivel->put($nivelKey, $nivel);
            }

            $descripcionKey = $this->descripcionKey($nivel->descripcion);

            if ($descripcionKey !== '' && ! $porDescripcion->has($descripcionKey)) {
                $porDescripcion->put($descripcionKey, $nivel);
            }
        }

        return [
            'cod_uom'     => $porCodUom,
            'nivel'       => $porNivel,
            'descripcion' => $porDescripcion,
        ];
    }

    private function codUomIoma(): ?string
    {
        $valor = Parametro::query()
            ->where('nombre', 'COBERTURA_COD_IOMA')
            ->value('valor');

        return $this->texto($valor);
    }

    /**
     * @return Collection<string, object>
     */
    private function mapaNomencladorIoma(): Collection
    {
        return DB::table('nomenclador')
            ->where('nom_padre_id', self::NOM_PADRE_IOMA)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get()
            ->keyBy(fn (object $fila) => $this->codigoKey($fila->codigo));
    }

    /**
     * Codigos de obra social del sistema para validar que el archivo se
     * pueda relacionar. Cada cobertura puede tener varios codigos UOM
     * separados por coma ("84,274"); se registran todos.
     *
     * @return array<string, bool>
     */
    private function codigosObraSocialSistema(): array
    {
        if ($this->codigosObraSocial !== null) {
            return $this->codigosObraSocial;
        }

        $codigos = [];

        foreach (Cobertura::query()->get(['cod_uom']) as $cobertura) {
            foreach ($this->codigosCobertura($cobertura->cod_uom) as $codigo => $_ignorar) {
                $codigos[$codigo] = true;
            }
        }

        return $this->codigosObraSocial = $codigos;
    }

    /**
     * Codigos UOM de una cobertura, normalizados. El campo admite varios
     * separados por coma, espacio o punto y coma.
     *
     * @return array<string, bool>
     */
    private function codigosCobertura(?string $codUom): array
    {
        $codigos = [];
        $texto = $this->texto($codUom);

        if ($texto === null) {
            return $codigos;
        }

        foreach (preg_split('/\s*[,;]\s*/u', $texto) ?: [] as $parte) {
            $codigo = $this->codigoKey($parte);

            if ($codigo !== '') {
                $codigos[$codigo] = true;
            }
        }

        return $codigos;
    }

    /**
     * Indica si el codigo del archivo esta entre los codigos UOM de la
     * cobertura (que puede tener varios separados por coma).
     */
    private function coincideCodUom(?string $codigoArchivo, ?string $codUomSistema): bool
    {
        $codigo = $this->codigoKey($codigoArchivo);

        if ($codigo === '') {
            return false;
        }

        return isset($this->codigosCobertura($codUomSistema)[$codigo]);
    }

    /**
     * Resuelve el cod_uom de la obra social que informa el archivo.
     *
     * Hay dos formatos de archivo:
     * - Con codigo: "143 - UOM" -> 143. Se usa ese codigo tal cual: la
     *   validacion luego lo busca en coberturas.cod_uom (que admite varios
     *   codigos separados por coma). No se usa la descripcion para matchear.
     * - Con nombre: "Obra Social - Plan: OSDE - 310 GRAVADO" -> se busca la
     *   sigla o el nombre de la cobertura en el sistema (OSDE -> 102).
     *
     * @return array{cod_uom: string|null, motivo: string|null}
     */
    private function resolverCodUomArchivo(?string $obraSocial): array
    {
        $codUom = $this->prefijoUom($obraSocial);

        if ($codUom !== null) {
            return ['cod_uom' => $codUom, 'motivo' => null];
        }

        if ($obraSocial === null || trim($obraSocial) === '') {
            return [
                'cod_uom' => null,
                'motivo'  => 'El archivo no informa la obra social.',
            ];
        }

        $clave   = $this->claveObraSocial($obraSocial);
        $nombres = $this->nombresObraSocialSistema();

        if ($clave !== '' && array_key_exists($clave, $nombres)) {
            $codUom = $nombres[$clave];

            if ($codUom !== null) {
                return ['cod_uom' => $codUom, 'motivo' => null];
            }

            return [
                'cod_uom' => null,
                'motivo'  => 'La obra social del archivo ('.$obraSocial.') no tiene cod_uom cargado en el sistema.',
            ];
        }

        return [
            'cod_uom' => null,
            'motivo'  => 'La obra social del archivo ('.$obraSocial.') no coincide con ninguna cobertura del sistema.',
        ];
    }

    /**
     * Sigla y nombre de las coberturas del sistema -> cod_uom.
     *
     * La sigla tiene prioridad sobre el nombre. Las coberturas sin cod_uom
     * quedan con null: se informa que no se puede relacionar.
     *
     * @return array<string, string|null>
     */
    private function nombresObraSocialSistema(): array
    {
        if ($this->nombresObraSocial !== null) {
            return $this->nombresObraSocial;
        }

        $siglas  = [];
        $nombres = [];

        foreach (Cobertura::query()->get(['nombre', 'sigla', 'cod_uom']) as $cobertura) {
            $codigos = $this->codigosCobertura($cobertura->cod_uom);

            // El 0 no es un codigo de obra social: son coberturas todavia sin
            // codificar.
            unset($codigos['0']);

            $codUom = $codigos === [] ? null : (string) array_key_first($codigos);

            $sigla = $this->claveObraSocial($this->texto($cobertura->sigla));

            if ($sigla !== '' && ! array_key_exists($sigla, $siglas)) {
                $siglas[$sigla] = $codUom;
            }

            $nombre = $this->claveObraSocial($this->texto($cobertura->nombre));

            if ($nombre !== '' && ! array_key_exists($nombre, $nombres)) {
                $nombres[$nombre] = $codUom;
            }
        }

        return $this->nombresObraSocial = array_merge($nombres, $siglas);
    }

    /**
     * Clave de comparacion de una obra social: solo letras y numeros en
     * mayusculas, sin acentos ni signos.
     * Ej.: "O.S.PERS.A.A.M.S" -> "OSPERS AAMS" -> "OSPERSAAMS".
     */
    private function claveObraSocial(?string $nombre): string
    {
        if ($nombre === null) {
            return '';
        }

        $nombre = Str::ascii(mb_strtoupper(trim($nombre)));

        return (string) preg_replace('/[^A-Z0-9]+/', '', $nombre);
    }

    private function esCoberturaIoma(?Cobertura $cobertura, ?string $codUomIoma): bool
    {
        if ($cobertura === null || $codUomIoma === null) {
            return false;
        }

        return isset($this->codigosCobertura($cobertura->cod_uom)[$this->codigoKey($codUomIoma)]);
    }

    /**
     * Separa el porcentaje que aparece en la descripcion del archivo. Se toma
     * el primer numero seguido de `%` y se descarta cualquier texto posterior.
     * Ej.: "Especial F 30%", "Especial F 50% valor" y
     * "Especial D 30% valor base" devuelven ["Especial F"|"Especial D", 30|50].
     * Tambien acepta espacios no separables (NBSP) alrededor del porcentaje.
     *
     * @return array{0: string|null, 1: float|null}
     */
    private function separarPorcentajeDescripcion(?string $descripcion): array
    {
        $descripcion = $this->texto($descripcion);

        if ($descripcion === null) {
            return [null, null];
        }

        if (preg_match('/^(.*?)[\s\x{00A0}]*(\d{1,3}(?:[.,]\d+)?)[\s\x{00A0}]*%.*$/iu', $descripcion, $m) !== 1) {
            return [$descripcion, null];
        }

        $base = trim($m[1]);
        $porcentaje = (float) str_replace(',', '.', $m[2]);

        return [$base === '' ? null : $base, $porcentaje];
    }

    private function descripcionKey(?string $descripcion): string
    {
        return $this->normalizarTexto($descripcion);
    }

    private function porcentajeKey(mixed $porcentaje): string
    {
        if ($porcentaje === null || $porcentaje === '') {
            return '';
        }

        return (string) (float) $porcentaje;
    }

    private function nroOpUnico(string $nroOp, int $documentoId): string
    {
        $ocupado = OrdenPagoAuditCab::query()
            ->where('nro_op', $nroOp)
            ->where(function ($query) use ($documentoId) {
                $query->whereNull('documento_llamaparse_id')
                    ->orWhere('documento_llamaparse_id', '!=', $documentoId);
            })
            ->exists();

        if (! $ocupado) {
            return $nroOp;
        }

        return Str::limit($nroOp, 12, '').'-'.$documentoId;
    }

    private function severidad(bool $difValor, float $porcentaje, bool $hayDiferencia): string
    {
        if ($difValor) {
            if ($porcentaje <= 5) {
                return 'baja';
            }

            if ($porcentaje <= 20) {
                return 'media';
            }

            return 'alta';
        }

        return $hayDiferencia ? 'baja' : 'sin-diferencia';
    }

    private function porcentajeDiferencia(float $diferencia, float $valorSistema): float
    {
        $base = max(abs($valorSistema), 0.01);

        return round(abs($diferencia) / $base * 100, 2);
    }

    private function fechasDistintas(mixed $fechaArchivo, mixed $fechaSistema): bool
    {
        if ($fechaArchivo === null || $fechaSistema === null) {
            return false;
        }

        $archivo = $fechaArchivo instanceof Carbon
            ? $fechaArchivo->toDateString()
            : (string) $fechaArchivo;
        $sistema = $fechaSistema instanceof Carbon
            ? $fechaSistema->toDateString()
            : (string) $fechaSistema;

        return $archivo !== '' && $sistema !== '' && $archivo !== $sistema;
    }

    private function textosDistintos(?string $archivo, ?string $sistema): bool
    {
        $archivo = $this->normalizarTexto($archivo);
        $sistema = $this->normalizarTexto($sistema);

        if ($archivo === '' || $sistema === '') {
            return false;
        }

        return $archivo !== $sistema;
    }

    private function normalizarTexto(?string $texto): string
    {
        if ($texto === null) {
            return '';
        }

        $texto = mb_strtoupper(trim($texto));
        $texto = strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'Ü' => 'U', 'Ñ' => 'N',
        ]);

        return (string) preg_replace('/[\s\x{00A0}]+/u', ' ', $texto);
    }

    /**
     * Normaliza un nombre para compararlo: mayusculas, sin acentos, sin
     * comas/puntos y con un solo espacio entre palabras.
     */
    private function normalizarNombre(?string $nombre): string
    {
        $nombre = $this->normalizarTexto($nombre);
        $nombre = (string) preg_replace('/[^A-Z0-9 ]+/u', ' ', $nombre);

        return trim((string) preg_replace('/\s+/', ' ', $nombre));
    }

    /**
     * Palabras significativas de un nombre (descarta prefijos como Dr. o Lic.
     * y palabras de menos de 3 letras).
     *
     * @return array<int, string>
     */
    private function tokensNombre(string $nombre): array
    {
        $tokens = preg_split('/\s+/', $nombre, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $ignorar = ['DR', 'DRA', 'LIC', 'PROF', 'SR', 'SRA', 'SRTA'];

        return array_values(array_filter(
            $tokens,
            fn (string $token) => mb_strlen($token) >= 3 && ! in_array($token, $ignorar, true)
        ));
    }

    /**
     * Detecta etiquetas de encabezado que el parser pudo haber tomado como
     * valor (por ejemplo, profesional = "CUIT").
     */
    private function esEtiquetaDeEncabezado(?string $valor): bool
    {
        if ($valor === null) {
            return false;
        }

        return in_array($this->normalizarTexto($valor), [
            'AGENTE',
            'CUIT',
            'DIRECCION',
            'FECHA',
            'CONCEPTO',
            'IMPORTE',
            'NETO',
            'IVA',
            'TOTAL',
            'NOMBRE',
            'APELLIDO',
            'DOMICILIO',
            'COND IVA',
            'COMPROBANTE',
            'OBSERVACIONES',
            'NUMERO',
        ], true);
    }

    /**
     * Deja solo los digitos de un CUIT (quita guiones, puntos y espacios).
     */
    private function soloDigitos(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $digitos = (string) preg_replace('/\D+/', '', $valor);

        return $digitos === '' ? null : $digitos;
    }

    private function prefijoUom(?string $texto): ?string
    {
        if ($texto === null) {
            return null;
        }

        if (preg_match('/^\s*(\d+)/', $texto, $m) === 1) {
            return ltrim($m[1], '0') === '' ? '0' : ltrim($m[1], '0');
        }

        return null;
    }

    private function codigoKey(mixed $codigo): string
    {
        $codigo = mb_strtoupper(trim((string) $codigo));

        if ($codigo === '') {
            return '';
        }

        if (ctype_digit($codigo)) {
            $codigo = ltrim($codigo, '0');

            return $codigo === '' ? '0' : $codigo;
        }

        return $codigo;
    }

    private function texto(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }

    private function fecha(mixed $valor): ?Carbon
    {
        if ($valor === null) {
            return null;
        }

        if ($valor instanceof Carbon) {
            return $valor;
        }

        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        foreach (['d/m/Y', 'd/m/y', 'Y-m-d', 'd-m-Y', 'd/m/Y H:i', 'Y-m-d H:i:s'] as $formato) {
            try {
                $fecha = Carbon::createFromFormat($formato, $valor);

                if ($fecha !== false && $fecha->isValid()) {
                    return $fecha->startOfDay();
                }
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($valor)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }
}
