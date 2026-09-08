<?php

namespace App\Services;

use App\Models\Consumo_cab;
use App\Models\Consumo_det;
use App\Models\Paciente;
use App\Models\Parte_cab;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use Illuminate\Support\Facades\Validator;

class PresupuestoParteService
{
    /**
     * Genera los partes (y consumos) a partir de un presupuesto.
     *
     * @return array{success: bool, message: string, errors: array<int, string>}
     */
    public function generar(int $id): array
    {
        $presupuesto = PresupuestoCab::where('id', $id)->first();

        if (! $presupuesto) {
            return [
                'success' => false,
                'message' => 'No se encontró el presupuesto.',
                'errors' => [],
            ];
        }

        $validation = $this->validateCab($presupuesto);
        if ($validation !== true) {
            return $validation;
        }

        $presup_det = PresupuestoDet::where('presupuesto_cab_id', $id)
            ->orderBy('cobertura_id', 'asc')
            ->get();

        $tmp_cobertura = 0;
        $consumo = null;
        $parte = null;

        foreach ($presup_det as $item) {
            if ($item->cobertura_id != $tmp_cobertura) {
                $tmp_cobertura = $item->cobertura_id;

                $paciente = Paciente::firstOrCreate(
                    ['dni' => $presupuesto->dni],
                    ['nombre' => $presupuesto->nombre, 'fec_nacimiento' => $presupuesto->fecha_nac]
                );

                $parte = new Parte_cab();
                $parte->profesional_id = $presupuesto->profesional_id;
                $parte->paciente_id = $paciente->id;
                $parte->gerenciadora_id = $presupuesto->gerenciadora_id;
                $parte->cobertura_id = $item->cobertura_id;
                $parte->centro_id = $presupuesto->centro_id;
                $parte->fec_prestacion = $presupuesto->fecha;
                $parte->fec_prestacion_fin = $presupuesto->fecha;
                $parte->user_id = $presupuesto->usuario_id;
                $parte->observaciones = $presupuesto->observaciones;
                $parte->estado_id = 4;
                $parte->save();

                $consumo = new Consumo_cab();
                $consumo->parte_cab_id = $parte->id;
                $consumo->user_id = $parte->user_id;
                $consumo->save();

                $presupuesto->parte_cab_id = $parte->id;
                $presupuesto->estado = 'F';
                $presupuesto->save();
            }

            $cons_det = new Consumo_det();
            $cons_det->consumo_cab_id = $consumo->id;
            $cons_det->nomenclador_id = $item->nomenclador_id;
            $cons_det->porcentaje = $item->porcentaje;
            $cons_det->cantidad = 1;
            $cons_det->valor = $item->valor;
            $cons_det->periodo = $item->periodo;
            $cons_det->estado_id = 4;
            $cons_det->obs_refac = $item->observaciones;
            $cons_det->nom_padre_id = $item->nom_padre_id;
            $cons_det->save();
        }

        return [
            'success' => true,
            'message' => 'Partes creados correctamente. Nro de parte generado: ' . $parte->id,
            'errors' => [],
        ];
    }

    /**
     * @return array{success: bool, message: string, errors: array<int, string>}|true
     */
    private function validateCab(PresupuestoCab $presupuesto): array|true
    {
        if ($presupuesto->parte_cab_id !== null) {
            return [
                'success' => false,
                'message' => 'Error: Ya se ha generado el parte anteriormente para este presupuesto, no es posible generar otro parte.',
                'errors' => [],
            ];
        }

        $validatorCab = Validator::make($presupuesto->toArray(), [
            'profesional_id' => 'required|integer',
            'gerenciadora_id' => 'required|integer',
            'centro_id' => 'required|integer',
            'usuario_id' => 'required|integer',
            'fecha' => 'required|date',
            'dni' => 'required|string',
            'nombre' => 'required|string',
            'fecha_nac' => 'required|date|before_or_equal:today'
        ], [
            'profesional_id.required' => 'Falta el profesional.',
            'gerenciadora_id.required' => 'Falta la gerenciadora.',
            'centro_id.required' => 'Falta el centro.',
            'usuario_id.required' => 'Falta el usuario.',
            'fecha.required' => 'Falta la fecha del presupuesto.',
            'dni.required' => 'Falta el DNI del paciente.',
            'nombre.required' => 'Falta el nombre del paciente.',
            'fecha_nac.required' => 'En la cabecera de presupuesto debe ingresarse la fecha de nacimiento del paciente.',
            'fecha_nac.before_or_equal' => 'La fecha de nacimiento no puede ser posterior a fecha de hoy.'
        ]);

        if ($validatorCab->fails()) {
            return [
                'success' => false,
                'message' => 'Hay campos obligatorios vacíos en la cabecera.',
                'errors' => $validatorCab->errors()->all(),
            ];
        }

        return true;
    }
}
