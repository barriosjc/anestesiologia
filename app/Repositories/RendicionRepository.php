<?php

namespace App\Repositories;

use App\Models\Consumo_det;
use App\Models\Estado;
use App\Models\Parte_cab;
use App\Models\Valores_cab;
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

    public function generarRendicion(array $selected, $periodo)
    {
        foreach ($selected as $consumoDetId) {
            $det = Consumo_det::find($consumoDetId);
            $det->periodo = $periodo;
            $det->estado_id = 5; // liquidado
            $det->save();

            $parte = Parte_cab::where('id', $this->parteIdFor($consumoDetId))->first();
            $parte->estado_id = 5;
            $parte->save();
        }

        return ['success' => true, 'mensaje' => 'Se generó la rendición.'];
    }

    public function cambiarEstados(array $selected, $nuevoEstado, $periodoRefac, $obsRefac)
    {
        try {
            if ($nuevoEstado == 7 && empty($periodoRefac)) {
                throw new InvalidArgumentException("Si el estado es 'A refacturar' es obligatorio ingresar el periodo.");
            }

            $sepa = '';
            $ids = '';
            foreach ($selected as $consumoDetId) {
                $consumo = Consumo_det::find($consumoDetId);
                $estActual = $consumo->estado_id;
                $parteId = $this->parteIdFor($consumoDetId);

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

                $consumo->estado_id = $nuevoEstado;
                if ($nuevoEstado == 7) {
                    $consumo->periodo = $periodoRefac;
                    $consumo->obs_refac = $obsRefac;
                }
                $consumo->save();
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

    public function revalorizar(array $selected, $periodo)
    {
        $cantidad = 0;
        foreach ($selected as $consumoDetId) {
            $rendicion = DB::table('v_rendiciones')->where('consumos_det_id', $consumoDetId)->first();

            $valores = Valores_cab::vValores(
                $rendicion->gerenciadora_id,
                $rendicion->cobertura_id,
                $rendicion->centro_id,
                $periodo,
                $rendicion->nivel
            );

            if (!empty($valores)) {
                $consumoDet = Consumo_det::find($consumoDetId);
                $consumoDet->valor = $valores->valor * ($rendicion->porcentaje / 100);
                $consumoDet->save();
                $cantidad++;
            }
        }

        return ['success' => true, 'mensaje' => "Se actualizaron los valores de {$cantidad} consumos."];
    }

    public function agregarConsumo(array $selected, $periodo, $estado, $valor, $observaciones)
    {
        if (count($selected) !== 1) {
            return ['success' => false, 'mensaje' => 'Para este proceso debe seleccionar solo (1) un consumo.'];
        }

        $original = Consumo_det::where('id', $selected[0])->first();
        $nuevo = $original->replicate();
        $nuevo->periodo = $periodo;
        $nuevo->estado_id = $estado;
        $nuevo->valor = $valor;
        $nuevo->obs_refac = $observaciones;
        $nuevo->save();

        return ['success' => true, 'mensaje' => 'Se agregó el nuevo consumo a la rendición.'];
    }

    public function agregarConsumoYDiferencia(array $selected, $periodo, $estado, $valor, $observaciones, $refacturar)
    {
        if (count($selected) !== 1) {
            return ['success' => false, 'mensaje' => 'Para este proceso debe seleccionar solo (1) un consumo.'];
        }

        $estados = Estado::all();
        $anulado = $estados->firstWhere('extra', 'anulado')->id;
        $original = Consumo_det::where('id', $selected[0])->first();
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

    protected function incrementarMes($dateString, $monthsToAdd)
    {
        $date = DateTime::createFromFormat('Y/m', $dateString);
        if (!$date) {
            throw new Exception("Fecha no válida: $dateString");
        }
        $date->modify("+{$monthsToAdd} month");

        return $date->format('Y/m');
    }
}
