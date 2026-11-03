<?php

namespace App\Repositories;

use App\Models\ConsumoDet;
use App\Models\Estado;
use App\Models\ParteCab;
use App\Models\ValoresCab;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class RendicionRepository
{
    protected function parteIdFor($consumoDetId)
    {
        return DB::table('v_rendiciones')->where('consumos_det_id', $consumoDetId)->value('parte_cab_id');
    }

    public function generarRendicion(array $selected, string $periodo): array
    {
        foreach ($selected as $consumoDetId) {
            $det = ConsumoDet::find($consumoDetId);
            $det->periodo = $periodo;
            $det->estado_id = 5; // liquidado
            $det->save();

            $parte = ParteCab::where('id', $this->parteIdFor($consumoDetId))->first();
            $parte->estado_id = 5;
            $parte->save();
        }

        return ['success' => true, 'mensaje' => 'Se generó la rendición.'];
    }

    /**
     * Cambia el estado de los consumos indicados y del parte asociado.
     *
     * @param  bool  $forzar  Omite la validacion del estado previo. La
     *                        auditoria de ordenes de pago debe poder pasar a
     *                        refacturar cualquier consumo, sin importar en
     *                        que estado se encuentre.
     */
    public function cambiarEstados(array $selected, int $nuevoEstado, ?string $periodoRefac, ?string $obsRefac, bool $forzar = false): array
    {
        try {
            if ($nuevoEstado == 7 && empty($periodoRefac)) {
                throw new InvalidArgumentException("Si el estado es 'A refacturar' es obligatorio ingresar el periodo.");
            }

            $sepa = '';
            $ids = '';
            foreach ($selected as $consumoDetId) {
                $consumo = ConsumoDet::find($consumoDetId);
                $estActual = $consumo->estado_id;
                $parteId = $this->parteIdFor($consumoDetId);

                if (! $forzar) {
                    if ($nuevoEstado == 5 && !in_array($estActual, [6, 7, 8])) {
                        $ids .= $sepa . $parteId;
                        $sepa = ' ,';
                        continue;
                    }
                    if ($nuevoEstado == 7 && !in_array($estActual, [5, 8])) {
                        $ids .= $sepa . $parteId;
                        $sepa = ' ,';
                        continue;
                    }
                    if ($nuevoEstado == 8 && !in_array($estActual, [5, 7])) {
                        $ids .= $sepa . $parteId;
                        $sepa = ' ,';
                        continue;
                    }
                    if ($nuevoEstado <= 5) {
                        $ids .= $sepa . $parteId;
                        $sepa = ' ,';
                        continue;
                    }
                    if ($nuevoEstado == 6 && in_array($estActual, [1, 2, 3, 4, 6, 9, 10])) {
                        $ids .= $sepa . $parteId;
                        $sepa = ' ,';
                        continue;
                    }
                }

                $consumo->estado_id = $nuevoEstado;
                if ($nuevoEstado == 7) {
                    $consumo->periodo = $periodoRefac;
                    $consumo->obs_refac = $obsRefac;
                }
                $consumo->save();

                // El estado del parte acompania al del consumo: quedan
                // siempre en el mismo estado.
                if ($parteId !== null) {
                    $parte = ParteCab::find($parteId);
                    if ($parte !== null && (int) $parte->estado_id !== (int) $nuevoEstado) {
                        $parte->estado_id = $nuevoEstado;
                        $parte->save();
                    }
                }
            }

            if (!empty($ids)) {
                throw new Exception($ids);
            }

            return ['success' => true, 'mensaje' => 'Estado/s actualizado/s con éxito.'];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'mensaje' => $e->getMessage() . '.'];
        } catch (Throwable $e) {
            return ['success' => false, 'mensaje' => 'Algun/os Detalles de rendición no se permite el cambio de estado seleccionado en base a su estado previo a realizar el cambio, nros de parte: ' . $e->getMessage() . '. '];
        }
    }

    public function revalorizar(array $selected, string $periodo): array
    {
        $cantidad = 0;
        foreach ($selected as $consumoDetId) {
            $rendicion = DB::table('v_rendiciones')->where('consumos_det_id', $consumoDetId)->first();

            $valores = ValoresCab::vValores(
                $rendicion->gerenciadora_id,
                $rendicion->cobertura_id,
                $rendicion->centro_id,
                $periodo,
                $rendicion->nivel
            );

            if (!empty($valores)) {
                $consumoDet = ConsumoDet::find($consumoDetId);
                $consumoDet->valor = $valores->valor * ($rendicion->porcentaje / 100);
                $consumoDet->save();
                $cantidad++;
            }
        }

        return ['success' => true, 'mensaje' => "Se actualizaron los valores de {$cantidad} consumos."];
    }

    /**
     * Busca los valores de una practica para el periodo indicado. Si ese
     * periodo no tiene valores cargados, usa el ultimo periodo anterior que
     * tenga valores para la misma gerenciadora, cobertura, centro y nivel.
     *
     * @return array{valores: ?ValoresCab, periodo: string}
     */
    public function valoresPractica(int $gerenciadoraId, int $coberturaId, int $centroId, string $periodo, string $nivel): array
    {
        $periodos = DB::table('nom_valores_cab')
            ->where('gerenciadora_id', $gerenciadoraId)
            ->where('cobertura_id', $coberturaId)
            ->where('centro_id', $centroId)
            ->where('periodo', '<=', $periodo)
            ->orderByDesc('periodo')
            ->pluck('periodo')
            ->unique();

        foreach ($periodos as $periodoValores) {
            $valores = ValoresCab::vValores($gerenciadoraId, $coberturaId, $centroId, $periodoValores, $nivel);

            if (!empty($valores)) {
                return ['valores' => $valores, 'periodo' => $periodoValores];
            }
        }

        return ['valores' => null, 'periodo' => $periodo];
    }

    /**
     * Agrega una nueva practica al parte del consumo marcado, tomando de este
     * los datos del detalle (consumo, periodo, estado, observaciones) y
     * valorizando la practica con el porcentaje indicado.
     */
    public function agregarNuevoItem(array $selected, int $nomencladorId, int $nomPadreId, float $porcentaje): array
    {
        if (count($selected) !== 1) {
            return ['success' => false, 'mensaje' => 'Para este proceso debe seleccionar solo (1) un consumo.'];
        }

        $rendicion = DB::table('v_rendiciones')->where('consumos_det_id', $selected[0])->first();
        $original = ConsumoDet::where('id', $selected[0])->first();

        if ($rendicion === null || $original === null) {
            return ['success' => false, 'mensaje' => 'No se encontró el consumo seleccionado.'];
        }

        $nivel = DB::table('nomenclador')
            ->where('id', $nomencladorId)
            ->where('nom_padre_id', $nomPadreId)
            ->value('nivel');

        if ($nivel === null) {
            $nivel = DB::table('nom_practicas_estudios')
                ->where('id', $nomencladorId)
                ->where('nom_padre_id', $nomPadreId)
                ->value('codigo');
        }

        if ($nivel === null) {
            return ['success' => false, 'mensaje' => 'No se encontró la práctica seleccionada.'];
        }

        if (empty($rendicion->periodo)) {
            return ['success' => false, 'mensaje' => 'El consumo seleccionado no tiene periodo para valorizar la práctica.'];
        }

        $busqueda = $this->valoresPractica(
            (int) $rendicion->gerenciadora_id,
            (int) $rendicion->cobertura_id,
            (int) $rendicion->centro_id,
            (string) $rendicion->periodo,
            (string) $nivel
        );

        if (empty($busqueda['valores'])) {
            return ['success' => false, 'mensaje' => 'No hay valores cargados para la práctica seleccionada.'];
        }

        $nuevo = $original->replicate();
        $nuevo->nomenclador_id = $nomencladorId;
        $nuevo->nom_padre_id = $nomPadreId;
        $nuevo->porcentaje = $porcentaje;
        $nuevo->cantidad = 1;
        $nuevo->valor = round($busqueda['valores']->valor * ($porcentaje / 100), 2);
        $nuevo->save();

        $mensaje = 'Se agregó la nueva práctica a la rendición.';
        if ($busqueda['periodo'] !== (string) $rendicion->periodo) {
            $mensaje .= " Se valorizó con los valores del periodo {$busqueda['periodo']} (el periodo {$rendicion->periodo} no tiene valores cargados).";
        }

        return ['success' => true, 'mensaje' => $mensaje];
    }

    public function agregarConsumo(array $selected, string $periodo, int $estado, $valor, string $observaciones): array
    {
        if (count($selected) !== 1) {
            return ['success' => false, 'mensaje' => 'Para este proceso debe seleccionar solo (1) un consumo.'];
        }

        $original = ConsumoDet::where('id', $selected[0])->first();
        $nuevo = $original->replicate();
        $nuevo->periodo = $periodo;
        $nuevo->estado_id = $estado;
        $nuevo->valor = $valor;
        $nuevo->obs_refac = $observaciones;
        $nuevo->save();

        return ['success' => true, 'mensaje' => 'Se agregó el nuevo consumo a la rendición.'];
    }

    public function agregarConsumoYDiferencia(array $selected, string $periodo, int $estado, $valor, string $observaciones, string $refacturar): array
    {
        if (count($selected) !== 1) {
            return ['success' => false, 'mensaje' => 'Para este proceso debe seleccionar solo (1) un consumo.'];
        }

        $estados = Estado::all();
        $anulado = $estados->firstWhere('extra', 'anulado')->id;
        $original = ConsumoDet::where('id', $selected[0])->first();
        $valorDiff = $original->valor - $valor;
        if ($valorDiff < 0) {
            return ['success' => false, 'mensaje' => 'El valor a agregar no puede ser mayor al valor original.'];
        }
        $original->estado_id = $anulado;
        $original->save();

        $nuevo = $original->replicate();
        $nuevo->periodo = $periodo;
        $nuevo->estado_id = $estado;
        $nuevo->valor = $valor;
        $nuevo->obs_refac = $observaciones;
        $nuevo->save();

        $estadoId = $refacturar === 'refacturar'
            ? $estados->firstWhere('extra', 'refacturar')->id
            : $estados->firstWhere('extra', 'auditoria')->id;

        $diferencia = $original->replicate();
        $diferencia->periodo = $this->incrementarMes($periodo, 1);
        $diferencia->estado_id = $estadoId;
        $diferencia->valor = $valorDiff;
        $diferencia->obs_refac = $observaciones;
        $diferencia->save();

        return ['success' => true, 'mensaje' => 'Se agregó el nuevo consumo y diferencia a la rendición.'];
    }

    protected function incrementarMes(string $dateString, int $monthsToAdd): string
    {
        $date = DateTime::createFromFormat('Y/m', $dateString);
        if (!$date) {
            throw new Exception("Fecha no válida: $dateString");
        }
        $date->modify("+{$monthsToAdd} month");

        return $date->format('Y/m');
    }
}
