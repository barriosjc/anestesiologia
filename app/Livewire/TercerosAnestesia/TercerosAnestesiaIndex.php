<?php

namespace App\Livewire\TercerosAnestesia;

use App\Enums\AnestesiaEstado;
use App\Models\Cobertura;
use App\Models\Nomenclador;
use App\Models\Profesional;
use App\Models\TerceroAnestesia;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class TercerosAnestesiaIndex extends Component
{
    use WithPagination;

    public string $busqueda = '';

    // Propiedades del formulario / modal
    public ?int $modalId = null;
    public string $fecha = '';
    public string $hora = '';
    public string $paciente = '';
    public ?int $nomenclador_id = null;
    public string $estado = 'asignado';
    public ?int $profesional_id = null;
    public ?int $cobertura_id = null;
    public int $urgencia = 0;
    public string $observaciones = '';
    public int $pasado_sistema = 0;

    protected function rules(): array
    {
        return [
            'fecha'          => ['required', 'date'],
            'hora'           => ['nullable'],
            'paciente'       => ['required', 'string', 'max:255'],
            'nomenclador_id' => ['nullable', 'integer', 'exists:nomenclador,id'],
            'estado'         => ['required', 'string', 'in:asignado,realizado,suspendido'],
            'profesional_id' => ['nullable', 'integer', 'exists:profesionales,id'],
            'cobertura_id'   => ['nullable', 'integer', 'exists:coberturas,id'],
            'urgencia'       => ['required', 'in:0,1'],
            'observaciones'  => ['nullable', 'string', 'max:2000'],
            'pasado_sistema' => ['required', 'in:0,1'],
        ];
    }

    protected array $messages = [
        'fecha.required'    => 'La fecha es obligatoria.',
        'paciente.required' => 'El nombre del paciente es obligatorio.',
        'estado.required'   => 'El estado es obligatorio.',
    ];

    public function mount(): void
    {
        $this->fecha = Carbon::today()->toDateString();
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function abrirModalNuevo(): void
    {
        $this->resetForm();
        $this->fecha = Carbon::today()->toDateString();
        $this->dispatch('open-modal', modal: 'tercerosAnestesiaModal');
        $this->dispatch('sincronizar-choices', [
            'nomenclador_id' => null,
            'profesional_id' => null,
            'cobertura_id'   => null,
        ]);
    }

    public function abrirModalEditar(int $id): void
    {
        $registro = TerceroAnestesia::findOrFail($id);

        $this->modalId        = $registro->id;
        $this->fecha          = $registro->fecha ? $registro->fecha->toDateString() : '';
        $this->hora           = $registro->hora ? substr($registro->hora, 0, 5) : '';
        $this->paciente       = $registro->paciente;
        $this->nomenclador_id = $registro->nomenclador_id;
        $this->estado         = $registro->estado->value;
        $this->profesional_id = $registro->profesional_id;
        $this->cobertura_id   = $registro->cobertura_id;
        $this->urgencia       = $registro->urgencia ? 1 : 0;
        $this->observaciones  = $registro->observaciones ?? '';
        $this->pasado_sistema = $registro->pasado_sistema ? 1 : 0;

        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'tercerosAnestesiaModal');
        $this->dispatch('sincronizar-choices', [
            'nomenclador_id' => $this->nomenclador_id,
            'profesional_id' => $this->profesional_id,
            'cobertura_id'   => $this->cobertura_id,
        ]);
    }

    public function guardar(): void
    {
        $this->validate();

        $datos = [
            'fecha'          => $this->fecha,
            'hora'           => $this->hora ? Carbon::parse($this->hora)->toTimeString() : null,
            'paciente'       => $this->paciente,
            'nomenclador_id' => $this->nomenclador_id ?: null,
            'estado'         => $this->estado,
            'profesional_id' => $this->profesional_id ?: null,
            'cobertura_id'   => $this->cobertura_id ?: null,
            'urgencia'       => (bool) $this->urgencia,
            'observaciones'  => $this->observaciones ?: null,
            'pasado_sistema' => (bool) $this->pasado_sistema,
        ];

        if ($this->modalId) {
            TerceroAnestesia::findOrFail($this->modalId)->update($datos);
            session()->flash('success', 'Registro actualizado correctamente.');
        } else {
            TerceroAnestesia::create($datos);
            session()->flash('success', 'Registro creado correctamente.');
        }

        $this->dispatch('close-modal', modal: 'tercerosAnestesiaModal');
        $this->resetForm();
    }

    public function borrar(int $id): void
    {
        TerceroAnestesia::findOrFail($id)->delete();
        session()->flash('success', 'Registro eliminado correctamente.');
        $this->resetPage();
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    private function resetForm(): void
    {
        $this->modalId        = null;
        $this->fecha          = '';
        $this->hora           = '';
        $this->paciente       = '';
        $this->nomenclador_id = null;
        $this->estado         = 'asignado';
        $this->profesional_id = null;
        $this->cobertura_id   = null;
        $this->urgencia       = 0;
        $this->observaciones  = '';
        $this->pasado_sistema = 0;
        $this->resetValidation();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $registros = TerceroAnestesia::query()
            ->with(['nomenclador', 'profesional', 'cobertura'])
            ->when($this->busqueda !== '', function ($q) {
                $q->where('paciente', 'like', '%' . $this->busqueda . '%');
            })
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(15);

        $nomencladores = Nomenclador::query()
            ->select('id', 'codigo', 'descripcion')
            ->orderBy('descripcion')
            ->get();

        $profesionales = Profesional::query()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        $coberturas = Cobertura::query()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return view('livewire.terceros-anestesia.terceros-anestesia-index', [
            'registros'     => $registros,
            'nomencladores' => $nomencladores,
            'profesionales' => $profesionales,
            'coberturas'    => $coberturas,
            'estados'       => AnestesiaEstado::cases(),
        ]);
    }
}
