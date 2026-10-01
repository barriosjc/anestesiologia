<?php

namespace App\Livewire\TercerosAnestesia;

use App\Enums\AnestesiaEstado;
use App\Models\Cobertura;
use App\Models\GerenciadoraCoberturaNomPadre;
use App\Models\Nomenclador;
use App\Models\Profesional;
use App\Models\TerceroAnestesia;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Alta / edición de un tercero con anestesia como pantalla completa (no modal).
 *
 * En celular el modal obligaba a un doble scroll (el body del modal scrolleaba
 * dentro de la página) y el teclado suave tapaba los desplegables de Choices.
 * Con una pantalla propia el formulario scrollea normal, el botón Guardar queda
 * siempre alcanzable y el "volver" del navegador funciona.
 */
#[Layout('layouts.main')]
class TercerosAnestesiaForm extends Component
{
    public ?int $tercero_id = null;

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

    public function mount(?int $id = null): void
    {
        $this->fecha = Carbon::today()->toDateString();

        if (! $id) {
            return;
        }

        $registro = TerceroAnestesia::find($id);

        if (! $registro) {
            session()->flash('error', 'El registro solicitado no existe o fue eliminado.');
            $this->redirect(route('terceros_anestesia.index'));

            return;
        }

        $this->tercero_id     = $registro->id;
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
    }

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

        if ($this->tercero_id) {
            TerceroAnestesia::findOrFail($this->tercero_id)->update($datos);
            session()->flash('success', 'Registro actualizado correctamente.');
        } else {
            TerceroAnestesia::create($datos);
            session()->flash('success', 'Registro creado correctamente.');
        }

        $this->redirect(route('terceros_anestesia.index'));
    }

    /**
     * Igual que en la carga de un parte/consumo: al elegir la cobertura se
     * recalculan los procedimientos (nomenclador) disponibles para esa cobertura
     * y se los envía al navegador para actualizar el select de Choices.js.
     */
    public function updatedCoberturaId(): void
    {
        $nomencladores = $this->nomencladoresDisponibles();

        // Si el procedimiento elegido ya no pertenece a la cobertura, se limpia.
        if ($this->nomenclador_id !== null && ! $nomencladores->contains('id', $this->nomenclador_id)) {
            $this->nomenclador_id = null;
        }

        $this->dispatch(
            'nomencladores-actualizados',
            opciones: $this->nomencladoresOpciones($nomencladores),
            firma: $this->firmaNomencladores($nomencladores),
            seleccionado: $this->nomenclador_id,
        );
    }

    /**
     * Nomencladores disponibles según la cobertura seleccionada. La relación
     * cobertura -> nomenclador se arma con los nomencladores padre asociados en
     * 'gerenciadoras_coberturas_nom_padres'. Sin cobertura se muestran todos
     * (registros viejos sin cobertura siguen siendo editables).
     *
     * @return \Illuminate\Support\Collection<int, Nomenclador>
     */
    protected function nomencladoresDisponibles(): \Illuminate\Support\Collection
    {
        return Nomenclador::query()
            ->select('id', 'codigo', 'descripcion')
            ->when($this->cobertura_id, function ($query) {
                $nomPadreIds = GerenciadoraCoberturaNomPadre::query()
                    ->where('cobertura_id', $this->cobertura_id)
                    ->pluck('nom_padre_id')
                    ->unique()
                    ->values()
                    ->all();

                $query->whereIn('nom_padre_id', $nomPadreIds);
            })
            ->orderBy('descripcion')
            ->get();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Nomenclador>  $nomencladores
     * @return array<int, array{value: string, label: string}>
     */
    protected function nomencladoresOpciones(\Illuminate\Support\Collection $nomencladores): array
    {
        return $nomencladores->map(fn ($nom) => [
            'value' => (string) $nom->id,
            'label' => '[' . $nom->codigo . '] ' . $nom->descripcion,
        ])->values()->all();
    }

    /**
     * Firma del listado (ids ordenados) para que el navegador no vuelva a
     * recargar el select cuando el mismo listado ya está cargado.
     *
     * @param  \Illuminate\Support\Collection<int, Nomenclador>  $nomencladores
     */
    protected function firmaNomencladores(\Illuminate\Support\Collection $nomencladores): string
    {
        return $nomencladores->pluck('id')->sort()->implode(',');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.terceros-anestesia.terceros-anestesia-form', [
            'nomencladores' => $this->nomencladoresDisponibles(),
            'profesionales' => Profesional::query()
                ->select('id', 'nombre')
                ->orderBy('nombre')
                ->get(),
            // Trae todas las columnas: la vista muestra 'sigla', que no está en
            // $fillable y por eso no llega si se limita el select().
            'coberturas'    => Cobertura::query()->orderBy('nombre')->get(),
            'estados'       => AnestesiaEstado::cases(),
        ]);
    }
}