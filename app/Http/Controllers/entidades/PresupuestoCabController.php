<?php

namespace App\Http\Controllers\entidades;

use App\Http\Controllers\Controller;
use App\Models\Consumo_cab;
use App\Models\Consumo_det;
use App\Models\Paciente;
use App\Models\Parte_cab;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;

class PresupuestoCabController extends Controller
{
    public function print(int $id, \App\Repositories\PresupuestoDetalleRepository $presupuestoDetalleRepository)
    {
        $presupuesto = PresupuestoCab::with(['pagos', 'centro', 'user'])->findOrFail($id);
        $presupuesto->presupuestosDet = $presupuestoDetalleRepository->detalleConDescripcion($id);

        $pdf = Pdf::loadView('reportes.Presupuestos.Informe', compact('presupuesto'));

        //return $pdf->stream('presupuesto_'.$presupuesto->id.'.pdf');
        // Si querés que se descargue automáticamente:
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="presupuesto_' . $presupuesto->id . '.pdf"');
    }

    public function partes(int $id)
    {
        $presupuesto = PresupuestoCab::where('id', $id)->first();

        // Validar ANTES del loop
        $validation = $this->validateCab($presupuesto);
        if ($validation !== true) {
            return $validation; // Retornar el redirect si falla
        }

        $presup_det = PresupuestoDet::where('presupuesto_cab_id', $id)
            ->orderBy('cobertura_id', 'asc')
            ->get();

        $tmp_cobertura = 0;
        $consumo = null; // Inicializar fuera del loop

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
            $cons_det->nomenclador_id = $item->nomenclador_id; // Era $presup_det, debe ser $item
            $cons_det->porcentaje = $item->porcentaje;
            $cons_det->cantidad = 1;
            $cons_det->valor = $item->valor;
            $cons_det->periodo = $item->periodo;
            $cons_det->estado_id = 4;
            $cons_det->obs_refac = $item->observaciones;
            $cons_det->nom_padre_id = $item->nom_padre_id;
            $cons_det->save();
        }

        // Retornar algo al finalizar exitosamente
        return redirect()->back()->with('success', 'Partes creados correctamente. Nro de parte generado: ' . $parte->id);
    }

    private function validateCab(PresupuestoCab $presupuesto)
    {
        if ($presupuesto->parte_cab_id !== null) {
            return redirect()
                ->back()
                ->with('error', 'Error: Ya se ha generado el parte anteriormnente para este presupuesto, no es posible generar otro parte.');
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
            'fecha_nac.required' => 'En la cabecera de presupuesto debe ingresda la fecha de naciento del paciente.',
            'fecha_nac.before_or_equal' => 'La fecha de nacimiento no puede ser posterior a fecha de hoy.'
        ]);

        if ($validatorCab->fails()) {
            return redirect()->back()
                ->withErrors($validatorCab)
                ->withInput()
                ->with('error', 'Hay campos obligatorios vacíos en la cabecera.');
        }
        return true; // Retornar true si la validación pasa
    }
}
