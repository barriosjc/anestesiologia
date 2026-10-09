<?php

namespace App\Livewire\TercerosAnestesia;

use App\Enums\AnestesiaEstado;
use App\Models\Cobertura;
use App\Models\GerenciadoraCoberturaNomPadre;
use App\Models\NomPracticasEstudio;
use App\Models\Nomenclador;
use App\Models\Profesional;
use App\Models\TerceroAnestesia;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Alta / edicion de un tercero con anestesia como pantalla completa (no modal).
 *
 * En celular el modal obligaba a un doble scroll (el body del modal scrolleaba
 * dentro de la pagina) y el teclado suave tapaba los desplegables de Choices.
 * Con una pantalla propia el formulario scrollea normal, el boton Guardar queda
 * siempre alcanzable y el "volver" del navegador funciona.
 */
#[Layout('layouts.main')]
class TercerosAnestesiaForm extends Component
{
    public ?int $tercero_id = null;

    public string $fecha = '';
    public string $hora = '';
    public string $paciente = '';
    public string $dni = '';
    public ?int $nomenclador_id = null;
    public string $estado = 'asignado';
    public ?int $profesional_id = null;
    public ?int $cobertura_id = null;
    public int $urgencia = 0;
    public string $observaciones = '';
    public int $pasado_sistema = 0;

    /**
     * nom_padre_id del procedimiento seleccionado. Se resuelve al
     * guardar (o al cargar en edicion) para poder distinguir si
     * nomenclador_id pertenece a 'nomenclador' o a
     * 'nom_practicas_estudios'.
     */
    public ?int $procedimiento_nom_padre_id = null;

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

        $this->tercero_id               = $registro->id;
        $this->fecha                    = $registro->fecha ? $registro->fecha->toDateString() : '';
        $this->hora                     = $registro->hora ? substr($registro->hora, 0, 5) : '';
        $this->paciente                 = $registro->paciente;
        $this->dni                      = $registro->dni ?? '';
        $this->nomenclador_id           = $registro->nomenclador_id;
        $this->procedimiento_nom_padre_id = $registro->nom_padre_id;
        $this->estado                   = $registro->estado->value;
        $this->profesional_id           = $registro->profesional_id;
        $this->cobertura_id             = $registro->cobertura_id;
        $this->urgencia                 = $registro->urgencia ? 1 : 0;
        $this->observaciones            = $registro->observaciones ?? '';
        $this->pasado_sistema           = $registro->pasado_sistema ? 1 : 0;
    }

    protected function rules(): array
    {
        return [
            'fecha'          => ['required', 'date'],
            'hora'           => ['nullable'],
            'paciente'       => ['required', 'string', 'max:255'],
            'dni'            => ['nullable', 'string', 'max:10'],
            'nomenclador_id' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                if ($value &&
                    ! Nomenclador::where('id', $value)->exists() &&
                    ! NomPracticasEstudio::where('id', $value)->exists()) {
                    $fail('El procedimiento seleccionado no es valido.');
                }
            }],
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
        'dni.max'           => 'El DNI no puede tener mas de 10 caracteres.',
        'estado.required'   => 'El estado es obligatorio.',
    ];

    public function guardar(): void
    {
        $this->validate();

        $nomPadreId = $this->resolverNomPadreProcedimiento();

        $datos = [
            'fecha'              => $this->fecha,
            'hora'               => $this->hora ? Carbon::parse($this->hora)->toTimeString() : null,
            'paciente'           => $this->paciente,
            'dni'                => $this->dni ?: null,
            'nomenclador_id'     => $this->nomenclador_id ?: null,
            'nom_padre_id'       => $nomPadreId,
            'estado'             => $this->estado,
            'profesional_id'     => $this->profesional_id ?: null,
            'cobertura_id'       => $this->cobertura_id ?: null,
            'urgencia'           => (bool) $this->urgencia,
            'observaciones'      => $this->observaciones ?: null,
            'pasado_sistema'     => (bool) $this->pasado_sistema,
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
     * Resuelve el nom_padre_id del procedimiento seleccionado buscandolo
     * en ambas tablas. Cuando hay cobertura, el filtro por nom_padre_ids
     * de la cobertura desambigua entre 'nomenclador' y
     * 'nom_practicas_estudios' (ambas tablas tienen autoincrementales
     * independientes que pueden colisionar).
     */
    protected function resolverNomPadreProcedimiento(): ?int
    {
        if (! $this->nomenclador_id) {
            return null;
        }

        $nomPadreIds = null;

        if ($this->cobertura_id) {
            $nomPadreIds = GerenciadoraCoberturaNomPadre::query()
                ->where('cobertura_id', $this->cobertura_id)
                ->pluck('nom_padre_id')
                ->unique()
                ->values()
                ->all();
        }

        // Intentar en nomenclador (con filtro de nom_padre si hay cobertura)
        $nomPadreId = Nomenclador::where('id', $this->nomenclador_id)
            ->when($nomPadreIds, function ($query) use ($nomPadreIds) {
                $query->whereIn('nom_padre_id', $nomPadreIds);
            })
            ->value('nom_padre_id');

        if ($nomPadreId) {
            return $nomPadreId;
        }

        // Intentar en nom_practicas_estudios
        return NomPracticasEstudio::where('id', $this->nomenclador_id)
            ->when($nomPadreIds, function ($query) use ($nomPadreIds) {
                $query->whereIn('nom_padre_id', $nomPadreIds);
            })
            ->value('nom_padre_id');
    }

    /**
     * Igual que en la carga de un parte/consumo: al elegir la cobertura se
     * recalculan los procedimientos (nomenclador) disponibles para esa cobertura
     * y se los envia al navegador para actualizar el select de Choices.js.
     */
    public function updatedCoberturaId(): void
    {
        $nomencladores = $this->nomencladoresDisponibles();

        // Si el procedimiento elegido ya no pertenece a la cobertura, se limpia.
        if ($this->nomenclador_id !== null && ! $nomencladores->contains('id', $this->nomenclador_id)) {
            $this->nomenclador_id = null;
            $this->procedimiento_nom_padre_id = null;
        }

        $this->dispatch(
            'nomencladores-actualizados',
            opciones: $this->nomencladoresOpciones($nomencladores),
            firma: $this->firmaNomencladores($nomencladores),
            seleccionado: $this->nomenclador_id,
        );
    }


    /**
     * Procedimientos disponibles segun la cobertura seleccionada.
     *
     * Combina Nomenclador y NomPracticasEstudio (misma logica que
     * NomencladoresServices::buscar() en presupuestos/auditoria carga):
     * la relacion cobertura -> procedimiento se arma con los nomencladores
     * padre asociados en 'gerenciadoras_coberturas_nom_padres'. Sin
     * cobertura se muestran todos (registros viejos sin cobertura siguen
     * siendo editables).
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    protected function nomencladoresDisponibles(): \Illuminate\Support\Collection
    {
        $nomPadreIds = $this->cobertura_id
            ? GerenciadoraCoberturaNomPadre::query()
                ->where('cobertura_id', $this->cobertura_id)
                ->pluck('nom_padre_id')
                ->unique()
                ->values()
                ->all()
            : null;

        // Nomenclador
        $nomencladores = Nomenclador::query()
            ->select('id', 'codigo', 'descripcion', 'nom_padre_id')
            ->when($nomPadreIds, function ($query) use ($nomPadreIds) {
                $query->whereIn('nom_padre_id', $nomPadreIds);
            })
            ->orderBy('descripcion')
            ->get()
            ->map(fn ($nom) => (object) [
                'id'           => $nom->id,
                'codigo'       => $nom->codigo,
                'descripcion'  => $nom->descripcion,
                'nom_padre_id' => $nom->nom_padre_id,
            ]);

        // NomPracticasEstudio (misma logica de filtrado por nom_padre_id)
        $practicas = NomPracticasEstudio::query()
            ->select('id', 'codigo', 'nombre', 'nom_padre_id')
            ->when($nomPadreIds, function ($query) use ($nomPadreIds) {
                $query->whereIn('nom_padre_id', $nomPadreIds);
            })
            ->orderBy('nombre')
            ->get()
            ->map(fn ($practica) => (object) [
                'id'           => $practica->id,
                'codigo'       => $practica->codigo,
                'descripcion'  => $practica->nombre,
                'nom_padre_id' => $practica->nom_padre_id,
            ]);

        return $nomencladores
            ->concat($practicas)
            ->sortBy('descripcion')
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $nomencladores
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
     * recargar el select cuando el mismo listado ya esta cargado.
     *
     * @param  \Illuminate\Support\Collection<int, object>  $nomencladores
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
            // Trae todas las columnas: la vista muestra 'sigla', que no esta en
            // $fillable y por eso no llega si se limita el select().
            'coberturas'    => Cobertura::query()->orderBy('nombre')->get(),
            'estados'       => AnestesiaEstado::cases(),
        ]);
    }
}