<?php

namespace App\Livewire\Consumos\Rendiciones;

use App\Models\Estado;
use App\Models\Periodo;
use App\Repositories\RendicionRepository;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class RendicionesTabla extends Component
{
    use WithPagination;

    public $filtros = [];
    public $selected = [];

    public $mensaje;
    public $mensajeTipo;

    public $periodo;

    public $estadoCambio;
    public $periodoRefac;
    public $obsRefac;

    public $periodoRevalorizar;

    public $estadoAgregar;
    public $periodoAgregar;
    public $obsAgregar;
    public $valorAgregar;

    public $estadoAgregarYDiff;
    public $periodoAgregarYDiff;
    public $obsAgregarYDiff;
    public $valorAgregarYDiff;
    public $refacturarYDiff = 'refacturar';

    public function mount()
    {
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
    public function aplicarFiltros($filtros)
    {
        $this->filtros = $filtros;
        $this->selected = [];
        $this->resetPage();
    }

    public function toggleAll($checked)
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
    protected function procesarAccion(array $reglas, array $mensajes, \Closure $accion)
    {
        $this->validate($reglas, $mensajes);

        $resultado = $accion();

        $this->mensajeTipo = $resultado['success'] ? 'success' : 'danger';
        $this->mensaje = $resultado['mensaje'];

        if ($resultado['success']) {
            $this->selected = [];
        }
    }

    public function generarRendicion(RendicionRepository $rendiciones)
    {
        $this->procesarAccion(
            ['selected' => 'required', 'periodo' => 'required'],
            [],
            fn () => $rendiciones->generarRendicion($this->selected, $this->periodo)
        );
    }

    public function cambiarEstados(RendicionRepository $rendiciones)
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

    public function revalorizar(RendicionRepository $rendiciones)
    {
        $this->procesarAccion(
            ['selected' => 'required', 'periodoRevalorizar' => 'required'],
            [],
            fn () => $rendiciones->revalorizar($this->selected, $this->periodoRevalorizar)
        );
    }

    public function agregarConsumo(RendicionRepository $rendiciones)
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

    public function agregarConsumoYDiferencia(RendicionRepository $rendiciones)
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

    public function paginationView()
    {
        return 'livewire::bootstrap';
    }

    public function render()
    {
        $partes = $this->buildQuery()->paginate();

        return view('livewire.consumos.rendiciones.rendiciones-tabla', [
            'partes' => $partes,
            'estados' => Estado::get(),
            'periodos' => Periodo::orderBy('nombre')->get(),
        ]);
    }
}
