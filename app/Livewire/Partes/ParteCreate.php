<?php

namespace App\Livewire\Partes;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Centro;
use App\Models\Paciente;
use App\Models\Cobertura;
use App\Models\ParteCab;
use App\Models\Profesional;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('layouts.main')]
class ParteCreate extends Component
{
    public ?int $parte_id = null;
    public ?int $gerenciadora_id = null;
    public ?int $centro_id = null;
    public ?string $fec_prestacion = null;
    public ?string $fec_prestacion_fin = null;
    public ?string $dni = null;
    public ?string $nombre = null;
    public ?string $fec_nacimiento = null;
    public ?int $cobertura_id = null;
    public ?int $profesional_id = null;
    public ?string $observaciones = null;

    public ?string $searchMessage = null;
    public ?string $searchMessageType = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $parte = ParteCab::find($id);
            $paciente = Paciente::find($parte->paciente_id);

            $this->parte_id = $parte->id;
            $this->gerenciadora_id = $parte->gerenciadora_id;
            $this->centro_id = $parte->centro_id;
            $this->fec_prestacion = $parte->fec_prestacion_input;
            $this->fec_prestacion_fin = $parte->fec_prestacion_fin_input;
            $this->dni = $paciente->dni;
            $this->nombre = $paciente->nombre;
            $this->fec_nacimiento = $paciente->fec_nacimiento;
            $this->cobertura_id = $parte->cobertura_id;
            $this->profesional_id = $parte->profesional_id;
            $this->observaciones = $parte->observaciones;
        }
    }

    public function searchPatient(): void
    {
        $this->reset('searchMessage', 'searchMessageType');

        if (empty($this->dni)) {
            $this->searchMessage = 'Ingrese un DNI';
            $this->searchMessageType = 'danger';
            return;
        }

        $paciente = Paciente::where('dni', $this->dni)->first();

        if ($paciente) {
            $this->nombre = $paciente->nombre;
            $this->fec_nacimiento = $paciente->fec_nacimiento;
            $this->searchMessage = 'Paciente encontrado';
            $this->searchMessageType = 'success';
        } else {
            $this->nombre = '';
            $this->fec_nacimiento = '';
            $this->searchMessage = 'No se encontró paciente con ese DNI';
            $this->searchMessageType = 'danger';
        }
    }

    public function save(): void
    {
        $this->validate();

        $paciente = Paciente::where('dni', $this->dni)->first();
        if (!$paciente) {
            $paciente = new Paciente();
        }
        $paciente->dni = $this->dni;
        $paciente->nombre = $this->nombre;
        $paciente->fec_nacimiento = $this->fec_nacimiento;
        $paciente->save();

        if ($this->parte_id) {
            $parte = ParteCab::find($this->parte_id);
            $msg = 'actualizado';
        } else {
            $parte = new ParteCab();
            $msg = 'creado';
        }

        $parte->paciente_id = $paciente->id;
        $parte->gerenciadora_id = $this->gerenciadora_id;
        $parte->centro_id = $this->centro_id;
        $parte->cobertura_id = $this->cobertura_id;
        $parte->profesional_id = $this->profesional_id;
        $parte->observaciones = $this->observaciones;
        $parte->fec_prestacion = $this->fec_prestacion;
        $parte->fec_prestacion_fin = $this->fec_prestacion_fin ?: $this->fec_prestacion;
        $parte->user_id = Auth()->user()->id;
        $parte->save();

        session()->flash('success', "Se ha {$msg} la cabecera del parte correctamente, nro: {$parte->id}.");

        $this->redirect(route('partes_det.create', $parte->id), navigate: true);
    }

    public function rules(): array
    {
        return [
            'dni' => 'required|string|max:15|regex:/^[0-9]+$/',
            'nombre' => 'required|string|min:2|max:250',
            'observaciones' => 'nullable|string|max:2000',
            'cobertura_id' => 'required|integer|exists:coberturas,id',
            'centro_id' => 'required|integer|exists:centros,id',
            'profesional_id' => 'required|integer|exists:profesionales,id',
            'gerenciadora_id' => 'required|integer|exists:gerenciadoras,id',
            'fec_nacimiento' => [
                'required',
                'date',
                'after_or_equal:1900-01-01',
                'before_or_equal:today',
                function ($attribute, $value, $fail) {
                    $year = explode('-', $value)[0];
                    if ($year < 1900 || $year > date('Y')) {
                        $fail("El campo fecha de nacimiento debe ser un año entre 1900 y " . date('Y') . ".");
                    }
                },
            ],
            'fec_prestacion' => 'required|date_format:Y-m-d\TH:i',
            'fec_prestacion_fin' => [
                'required',
                'date_format:Y-m-d\TH:i',
                function ($attribute, $value, $fail) {
                    if ($this->fec_prestacion) {
                        $ini = Carbon::createFromFormat('Y-m-d\TH:i', $this->fec_prestacion);
                        $fin = Carbon::createFromFormat('Y-m-d\TH:i', $value);
                        if ($fin->lt($ini)) {
                            $fail('La fecha de fin debe ser posterior a la fecha de presentación.');
                        }
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'El campo DNI es obligatorio.',
            'dni.max' => 'El DNI no puede superar los 15 caracteres.',
            'dni.regex' => 'El DNI debe contener solo números.',
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 250 caracteres.',
            'observaciones.max' => 'Las observaciones no pueden superar los 2000 caracteres.',
            'cobertura_id.required' => 'Debe seleccionar una cobertura.',
            'cobertura_id.exists' => 'La cobertura seleccionada no existe.',
            'centro_id.required' => 'Debe seleccionar un centro.',
            'centro_id.exists' => 'El centro seleccionado no existe.',
            'profesional_id.required' => 'Debe seleccionar un profesional.',
            'profesional_id.exists' => 'El profesional seleccionado no existe.',
            'gerenciadora_id.required' => 'Debe seleccionar una gerenciadora.',
            'gerenciadora_id.exists' => 'La gerenciadora seleccionada no existe.',
            'fec_nacimiento.required' => 'El campo fecha de nacimiento es obligatorio.',
            'fec_nacimiento.after_or_equal' => 'La fecha de nacimiento debe ser posterior al 01/01/1900.',
            'fec_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'fec_prestacion.required' => 'El campo fecha cirugía inicio es obligatorio.',
            'fec_prestacion.date_format' => 'El campo fecha cirugía inicio tiene un formato inválido.',
            'fec_prestacion_fin.required' => 'El campo fecha cirugía fin es obligatorio.',
            'fec_prestacion_fin.date_format' => 'El campo fecha cirugía fin tiene un formato inválido.',
        ];
    }

    public function updatedFecPrestacion(?string $value): void
    {
        if ($value && empty($this->fec_prestacion_fin)) {
            $this->fec_prestacion_fin = $value;
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();
        $gerenciadoras = $user->gerenciadoras;
        $centro_id = $user->centro_id;
        if ($centro_id) {
            $centros = Centro::where('id', $centro_id)->get();
        } else {
            $centros = Centro::orderBy('nombre')->get();
        }
        $coberturas = Cobertura::orderBy('nombre')->get();
        $profesionales = Profesional::get();

        return view('livewire.partes.parte-create', compact('gerenciadoras', 'centros', 'coberturas', 'profesionales'));
    }
}
