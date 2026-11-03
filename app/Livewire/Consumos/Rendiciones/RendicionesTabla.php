<?php

namespace App\Livewire\Consumos\Rendiciones;

use App\Models\Estado;
use App\Models\Periodo;
use App\Repositories\RendicionRepository;
use App\Services\NomencladoresServices;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class RendicionesTabla extends Component
{
    use WithPagination;

    public array $filtros = [];
    public array $selected = [];

    public ?string $mensaje = null;
    public ?string $mensajeTipo = null;

    public ?string $periodo = null;

    public ?int $estadoCambio = null;
    public ?string $periodoRefac = null;
    public ?string $obsRefac = null;

    public ?string $periodoRevalorizar = null;

    public ?int $estadoAgregar = null;
    public ?string $periodoAgregar = null;
    public ?string $obsAgregar = null;
    public ?string $valorAgregar = null;

    public ?int $estadoAgregarYDiff = null;
    public ?string $periodoAgregarYDiff = null;
    public ?string $obsAgregarYDiff = null;
    public ?string $valorAgregarYDiff = null;
    public string $refacturarYDiff = 'refacturar';

    // Nuevo item: práctica a agregar al detalle marcado en la grilla.
    public ?string $nuevoItemCodigo = null;
    public ?string $nuevoItemDescripcion = null;
    public array $nuevoItemOpciones = [];
    public $nuevoItemNomencladorId = null;
    public $nuevoItemNomPadreId = null;
    public $nuevoItemPorcentaje = 100;
    public $nuevoItemValor = 0;

    public function mount(): void
    {
        // Llegada desde un enlace directo (ej. auditoría de órdenes de pago):
        // se filtra por nro de parte sin arrastrar los filtros guardados en sesión.
        if (request()->filled('nro_parte')) {
            $this->filtros = [
                'cobertura_id' => null,
                'centro_id' => null,
                'profesional_id' => null,
                'nombre' => null,
                'fec_desde' => null,
                'fec_hasta' => null,
                'estado_id' => [],
                'periodo_gen' => null,
                'nro_parte' => (string) request()->query('nro_parte'),
            ];

            return;
        }

        $this->filtros = [
            'cobertura_id' => session('c_cobertura_id'),
            'centro_id' => session('c_centro_id'),
            'profesional_id' => session('c_profesional_id'),
            'nombre' => session('c_nombre'),
            'fec_desde' => session('c_fec_desde'),
            'fec_hasta' => session('c_fec_hasta'),
            'estado_id' => (array) session('c_estado_id', []),
            'periodo_gen' => session('c_periodo_gen'),
            'nro_parte' => session('c_nro_parte'),
        ];
    }

    #[On('filtros-aplicados')]
    public function aplicarFiltros(array $filtros): void
    {
        $this->filtros = $filtros;
        $this->selected = [];
        $this->resetPage();
    }

    public function toggleAll(bool $checked): void
    {
        if ($checked) {
            $this->selected = collect($this->buildQuery()->paginate()->items())
                ->pluck('consumos_det_id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected(): void
    {
        // El nuevo item se valoriza con el periodo del detalle marcado: al
        // cambiar la selección se recalcula el valor de la práctica elegida.
        if (count($this->selected) === 1) {
            $this->valorizarNuevoItem();
        } else {
            $this->nuevoItemValor = 0;
        }
    }

    /**
     * Detalle de la grilla del que se toman los datos para el nuevo item.
     */
    protected function itemSeleccionado(): ?object
    {
        if (count($this->selected) !== 1) {
            return null;
        }

        return DB::table('v_rendiciones')
            ->where('consumos_det_id', $this->selected[0])
            ->first();
    }

    protected function buildQuery()
    {
        $query = DB::table('v_rendiciones');

        if (!empty($this->filtros['cobertura_id'])) {
            $query->where('cobertura_id', '=', $this->filtros['cobertura_id']);
        }
        if (!empty($this->filtros['centro_id'])) {
            $query->where('centro_id', '=', $this->filtros['centro_id']);
        }
        if (!empty($this->filtros['profesional_id'])) {
            $query->where('profesional_id', '=', $this->filtros['profesional_id']);
        }
        if (!empty($this->filtros['estado_id'])) {
            $query->whereIn('estado_id', (array) $this->filtros['estado_id']);
        }
        if (!empty($this->filtros['nombre'])) {
            $query->where('pac_nombre', 'like', '%' . $this->filtros['nombre'] . '%');
        }
        if (!empty($this->filtros['fec_desde'])) {
            $query->where('fec_prestacion_orig', '>=', $this->filtros['fec_desde']);
        }
        if (!empty($this->filtros['fec_hasta'])) {
            $query->where('fec_prestacion_orig', '<=', $this->filtros['fec_hasta']);
        }
        if (!empty($this->filtros['periodo_gen'])) {
            $query->where('periodo', '=', $this->filtros['periodo_gen']);
        }
        if (!empty($this->filtros['nro_parte'])) {
            $query->where('parte_cab_id', '=', $this->filtros['nro_parte']);
        }

        return $query->orderBy('consumos_det_id', 'desc');
    }

    /**
     * Template method: valida, ejecuta la acción contra el repository y aplica el resultado.
     * Cada acción pública solo declara sus reglas propias y qué método del repository llamar.
     */
    protected function procesarAccion(array $reglas, array $mensajes, \Closure $accion): bool
    {
        $this->validate($reglas, $mensajes);

        $resultado = $accion();

        $this->mensajeTipo = $resultado['success'] ? 'success' : 'danger';
        $this->mensaje = $resultado['mensaje'];

        if ($resultado['success']) {
            $this->selected = [];
        }

        return $resultado['success'];
    }

    public function generarRendicion(RendicionRepository $rendiciones): void
    {
        $this->procesarAccion(
            ['selected' => 'required', 'periodo' => 'required'],
            [],
            fn () => $rendiciones->generarRendicion($this->selected, $this->periodo)
        );
    }

    public function cambiarEstados(RendicionRepository $rendiciones): void
    {
        $this->procesarAccion(
            [
                'selected' => 'required',
                'estadoCambio' => 'required',
                'periodoRefac' => $this->estadoCambio == 7 ? 'required' : 'nullable',
                'obsRefac' => $this->estadoCambio == 7 ? 'required|max:250' : 'nullable|max:250',
            ],
            [
                'selected.required' => 'Debe seleccionar de la grilla los procedimientos que requiere cambiar de estado.',
                'periodoRefac.required' => 'El campo periodo de refacturación es obligatorio cuando se quiere cambiar el estado A refacturar.',
                'obsRefac.required' => 'El campo Observaciones de refacturación es obligatorio cuando se quiere cambiar el estado A refacturar.',
            ],
            fn () => $rendiciones->cambiarEstados($this->selected, $this->estadoCambio, $this->periodoRefac, $this->obsRefac)
        );
    }

    public function revalorizar(RendicionRepository $rendiciones): void
    {
        $this->procesarAccion(
            ['selected' => 'required', 'periodoRevalorizar' => 'required'],
            [],
            fn () => $rendiciones->revalorizar($this->selected, $this->periodoRevalorizar)
        );
    }

    public function buscarPracticaNuevoItem(NomencladoresServices $nomencladores): void
    {
        $this->resetValidation(['nuevoItemCodigo', 'nuevoItemNomencladorId', 'nuevoItemPorcentaje']);
        $this->nuevoItemOpciones = [];
        $this->nuevoItemNomencladorId = null;
        $this->nuevoItemNomPadreId = null;
        $this->nuevoItemValor = 0;

        $item = $this->itemSeleccionado();
        if ($item === null) {
            $this->addError('nuevoItemCodigo', 'Debe marcar un solo ítem de la grilla para tomar sus datos.');

            return;
        }

        $termino = trim((string) ($this->nuevoItemCodigo ?: $this->nuevoItemDescripcion));
        if ($termino === '') {
            $this->addError('nuevoItemCodigo', 'Ingrese un código o una descripción para buscar la práctica.');

            return;
        }

        $this->nuevoItemOpciones = $nomencladores->buscar(
            $termino,
            null,
            $item->gerenciadora_id,
            $item->cobertura_id
        );

        if (empty($this->nuevoItemOpciones)) {
            $this->addError('nuevoItemCodigo', 'No se encontraron prácticas para la búsqueda.');

            return;
        }

        if (count($this->nuevoItemOpciones) === 1) {
            $this->nuevoItemNomencladorId = $this->nuevoItemOpciones[0]['id'];
            $this->updatedNuevoItemNomencladorId();
        }
    }

    public function updatedNuevoItemNomencladorId(): void
    {
        $opcion = collect($this->nuevoItemOpciones)->firstWhere('id', $this->nuevoItemNomencladorId);
        $this->nuevoItemNomPadreId = $opcion['nom_padre_id'] ?? null;
        $this->valorizarNuevoItem();
    }

    public function updatedNuevoItemPorcentaje(): void
    {
        $this->valorizarNuevoItem();
    }

    protected function valorizarNuevoItem(): void
    {
        $this->nuevoItemValor = 0;

        $item = $this->itemSeleccionado();
        $opcion = collect($this->nuevoItemOpciones)->firstWhere('id', $this->nuevoItemNomencladorId);

        if ($item === null || empty($opcion) || empty($item->periodo)) {
            return;
        }

        $busqueda = app(RendicionRepository::class)->valoresPractica(
            (int) $item->gerenciadora_id,
            (int) $item->cobertura_id,
            (int) $item->centro_id,
            (string) $item->periodo,
            (string) $opcion['nivel']
        );

        if (!empty($busqueda['valores'])) {
            $porcentaje = (float) ($this->nuevoItemPorcentaje ?: 100);
            $this->nuevoItemValor = round($busqueda['valores']->valor * ($porcentaje / 100), 2);
        }
    }

    protected function resetNuevoItem(): void
    {
        $this->nuevoItemCodigo = null;
        $this->nuevoItemDescripcion = null;
        $this->nuevoItemOpciones = [];
        $this->nuevoItemNomencladorId = null;
        $this->nuevoItemNomPadreId = null;
        $this->nuevoItemPorcentaje = 100;
        $this->nuevoItemValor = 0;
    }

    public function agregarNuevoItem(RendicionRepository $rendiciones): void
    {
        $agregado = $this->procesarAccion(
            [
                'selected' => 'required',
                'nuevoItemNomencladorId' => 'required',
                'nuevoItemNomPadreId' => 'required',
                'nuevoItemPorcentaje' => 'required|numeric|between:1,200',
            ],
            [
                'selected.required' => 'Debe marcar el ítem de la grilla del cual tomar los datos.',
                'nuevoItemNomencladorId.required' => 'Debe buscar y seleccionar la práctica a agregar.',
                'nuevoItemPorcentaje.required' => 'Debe ingresar el porcentaje.',
                'nuevoItemPorcentaje.between' => 'El porcentaje debe estar entre 1 y 200.',
            ],
            fn () => $rendiciones->agregarNuevoItem(
                $this->selected,
                (int) $this->nuevoItemNomencladorId,
                (int) $this->nuevoItemNomPadreId,
                (float) $this->nuevoItemPorcentaje
            )
        );

        if ($agregado) {
            $this->resetNuevoItem();
            $this->resetValidation();
        }
    }

    public function agregarConsumo(RendicionRepository $rendiciones): void
    {
        $this->procesarAccion(
            [
                'selected' => 'required',
                'periodoAgregar' => 'required',
                'estadoAgregar' => 'required',
                'valorAgregar' => 'required',
                'obsAgregar' => 'required|max:250',
            ],
            [],
            fn () => $rendiciones->agregarConsumo($this->selected, $this->periodoAgregar, $this->estadoAgregar, $this->valorAgregar, $this->obsAgregar)
        );
    }

    public function agregarConsumoYDiferencia(RendicionRepository $rendiciones): void
    {
        $this->procesarAccion(
            [
                'selected' => 'required',
                'periodoAgregarYDiff' => 'required',
                'estadoAgregarYDiff' => 'required',
                'valorAgregarYDiff' => 'required|numeric|max:999999999.99',
                'obsAgregarYDiff' => 'required|max:250',
            ],
            [],
            fn () => $rendiciones->agregarConsumoYDiferencia(
                $this->selected,
                $this->periodoAgregarYDiff,
                $this->estadoAgregarYDiff,
                $this->valorAgregarYDiff,
                $this->obsAgregarYDiff,
                $this->refacturarYDiff
            )
        );
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $partes = $this->buildQuery()->paginate();

        return view('livewire.consumos.rendiciones.rendiciones-tabla', [
            'partes' => $partes,
            'estados' => Estado::get(),
            'periodos' => Periodo::orderBy('nombre')->get(),
            'itemNuevo' => $this->itemSeleccionado(),
        ]);
    }
}
