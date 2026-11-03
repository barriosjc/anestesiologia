<?php

namespace App\Livewire\OrdenesPagosAudit;

use App\Models\ConsumoDet;
use App\Models\Estado;
use App\Models\OrdenPagoAuditCab;
use App\Models\ParteCab;
use App\Models\Periodo;
use App\Repositories\RendicionRepository;
use App\Services\OrdenPagoAuditCompareService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class OrdenPagoAuditDetalle extends Component
{
    use WithPagination;

    /**
     * Filas por pagina de la grilla.
     */
    private const POR_PAGINA = 50;

    /**
     * Estados que corresponden a un cambio de estado del consumo.
     */
    private const ESTADO_PAGADO = 6;

    private const ESTADO_REFACTURAR = 7;

    /**
     * Estado del parte que requiere el numero de atencion cargado.
     */
    private const ESTADO_A_LIQUIDAR = 3;

    /**
     * Columnas por las que se puede ordenar la tabla.
     *
     * @var array<string, string>
     */
    private const COLUMNAS_ORDEN = [
        'atencion'         => 'atencion',
        'fecha'            => 'fecha_archivo',
        'obra_social'      => 'cod_uom_archivo',
        'periodo'          => 'periodo_archivo',
        'codigo'           => 'codigo_archivo',
        'descripcion'      => 'descripcion_archivo',
        'porcentaje_nivel' => 'porcentaje_archivo',
        'valor_archivo'    => 'valor_archivo',
        'valor_sistema'    => 'valor_sistema',
        'diferencia'       => 'diferencia',
        'porcentaje_dif'   => 'porcentaje_diferencia',
        'estado'           => 'estado',
    ];

    /**
     * Etiquetas de las columnas, para mostrar el orden activo.
     *
     * @var array<string, string>
     */
    private const ETIQUETAS_ORDEN = [
        'atencion'         => 'Atención + Obra social',
        'fecha'            => 'Fecha',
        'obra_social'      => 'Obra social + Atención',
        'periodo'          => 'Período',
        'codigo'           => 'Código / nivel',
        'descripcion'      => 'Descripción',
        'porcentaje_nivel' => '% nivel',
        'valor_archivo'    => 'Valor archivo',
        'valor_sistema'    => 'Valor sistema',
        'diferencia'       => 'Diferencia',
        'porcentaje_dif'   => '% dif.',
        'estado'           => 'Estado',
    ];

    public OrdenPagoAuditCab $orden;

    public string $busqueda = '';
    public string $filtroEstado = '';
    public string $filtroSeveridad = '';
    public bool $soloDiferencias = false;

    /** Columna por la que se ordena (vacio = orden por gravedad). */
    public string $ordenPor = '';

    /** asc o desc. */
    public string $ordenDireccion = 'asc';

    /** Datos sin codigo detectados al recalcular (la auditoria no se guarda). */
    public array $inconsistenciasAuditoria = [];

    /** Ids de ordenes_pagos_audit_det seleccionados con los checkbox. */
    public array $seleccionados = [];

    /** Fila que se pasa a refacturar desde el menu de acciones. */
    public ?int $filaRefac = null;

    /** Periodo elegido en el modal de refacturacion. */
    public ?string $periodoRefac = null;

    /** Parte cuyo estado se cambia desde la columna de acciones. */
    public ?int $estado_cambio_id = null;
    public ?string $estado_cambio_obs = null;
    public ?int $estado_cambio_estado = null;

    /** Fila de la auditoria cuyo estado se esta cambiando. */
    public ?int $filaEstado = null;

    /** Resultado de la ultima accion de cambio de estado. */
    public ?string $mensaje = null;
    public ?string $mensajeTipo = null;

    public function mount(OrdenPagoAuditCab $orden): void
    {
        $this->orden = $orden->load(['profesional', 'documento']);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSeveridad(): void
    {
        $this->resetPage();
    }

    public function updatingSoloDiferencias(): void
    {
        $this->resetPage();
    }

    public function reauditar(): void
    {
        $documento = $this->orden->documento;

        if ($documento === null) {
            session()->flash('error', 'Esta auditoria no tiene un documento de origen para volver a comparar.');

            return;
        }

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->inconsistenciasAuditoria = $resultado['inconsistencias'] ?? [];

        if (! $resultado['success']) {
            session()->flash('error', $resultado['message']);

            return;
        }

        // Las filas se regeneran con otros ids: la seleccion anterior ya no
        // corresponde a nada.
        $this->seleccionados = [];
        $this->filaRefac = null;

        $this->orden->refresh()->load(['profesional', 'documento']);
        $this->resetPage();

        session()->flash('success', 'Auditoria recalculada correctamente.');
    }

    public function limpiar(): void
    {
        $this->reset(['busqueda', 'filtroEstado', 'filtroSeveridad', 'soloDiferencias']);
        $this->resetPage();
    }

    /**
     * Marca o desmarca todas las filas que se ven en la pantalla actual.
     */
    public function toggleAll(bool $seleccionar): void
    {
        if (! $seleccionar) {
            $this->seleccionados = [];

            return;
        }

        $ids = $this->detallesQuery()
            ->forPage($this->getPage(), self::POR_PAGINA)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->seleccionados = array_values(array_unique(array_merge($this->seleccionados, $ids)));
    }

    /**
     * Pasa a pagado los consumos de las filas seleccionadas.
     * Solo afecta a las filas que tienen el checkbox marcado y tienen un consumo del sistema asociado.
     */
    public function pasarAPagado(): void
    {
        $consumoDetIds = $this->consumoDetIdsDeFilas($this->seleccionados);

        $this->aplicarEstado(
            self::ESTADO_PAGADO,
            $consumoDetIds
        );
    }

    /**
     * Pasa a pagado el consumo de una unica fila (menu de acciones).
     */
    public function pasarAPagadoFila(int $filaId): void
    {
        $this->aplicarEstado(self::ESTADO_PAGADO, $this->consumoDetIdsDeFilas([$filaId]));
    }

    /**
     * Abre el modal de refacturacion para una fila, con el periodo actual
     * seleccionado.
     */
    public function abrirRefacturar(int $filaId): void
    {
        $this->mensaje = null;
        $this->mensajeTipo = null;

        $tieneConsumo = $this->orden->detalles()
            ->whereKey($filaId)
            ->whereNotNull('consumo_det_id')
            ->exists();

        if (! $tieneConsumo) {
            $this->mensajeTipo = 'warning';
            $this->mensaje = 'Esta fila no tiene un consumo del sistema asociado para refacturar.';

            return;
        }

        $this->filaRefac = $filaId;
        $this->periodoRefac = $this->periodoActual();

        $this->dispatch('open-modal', modal: 'opaRefacturarModal');
    }

    /**
     * Abre el modal de cambio de estado del parte asociado a una fila.
     */
    public function abrirEstadoModal(int $filaId): void
    {
        $this->mensaje = null;
        $this->mensajeTipo = null;

        $detalle = $this->orden->detalles()->with('parte')->find($filaId);
        $parte = $detalle?->parte;

        if ($parte === null) {
            $this->mensajeTipo = 'warning';
            $this->mensaje = 'Esta fila no tiene un parte del sistema asociado.';

            return;
        }

        $this->estado_cambio_id = $parte->id;
        $this->estado_cambio_obs = $parte->observaciones;
        $this->estado_cambio_estado = null;
        $this->filaEstado = $filaId;
        $this->resetValidation('estado_cambio_estado');

        $this->dispatch('open-modal', modal: 'valorModal');
    }

    /**
     * Guarda el estado elegido en el modal del parte.
     */
    public function cambiarEstado(): void
    {
        $this->validate(
            ['estado_cambio_estado' => 'required'],
            ['estado_cambio_estado.required' => '¡Atención! La selección del estado es obligatoria.']
        );

        $parte = ParteCab::findOrFail($this->estado_cambio_id);

        if ((int) $this->estado_cambio_estado === self::ESTADO_A_LIQUIDAR && blank($parte->atencion)) {
            $this->addError('estado_cambio_estado', 'Para pasar el parte a "A liquidar" debe cargar el Nro de atención en la pantalla de cabecera.');

            return;
        }

        $parte->observaciones = strip_tags((string) $this->estado_cambio_obs);
        $parte->estado_id = $this->estado_cambio_estado;
        $parte->save();

        // La pill de acciones muestra el estado del consumo: se mantiene
        // sincronizado con el estado del parte que se acaba de guardar.
        if ($this->filaEstado !== null) {
            $detalle = $this->orden->detalles()->find($this->filaEstado);

            if ($detalle?->consumo_det_id) {
                $consumo = ConsumoDet::find($detalle->consumo_det_id);

                if ($consumo !== null) {
                    $consumo->estado_id = $this->estado_cambio_estado;
                    $consumo->save();
                }
            }
        }

        $this->reset('estado_cambio_id', 'estado_cambio_obs', 'estado_cambio_estado', 'filaEstado');
        $this->dispatch('close-modal', modal: 'valorModal');

        session()->flash('success', 'Estado del consumo cambiado exitosamente.');
    }

    /**
     * Confirma el modal: pasa a refacturar el consumo de la fila elegida.
     */
    public function cambiarEstadoRefacturar(): void
    {
        $this->validate(
            ['periodoRefac' => 'required|string|max:10'],
            ['periodoRefac.required' => 'Elegí el período a refacturar.']
        );

        if ($this->filaRefac === null) {
            return;
        }

        $this->aplicarEstado(
            self::ESTADO_REFACTURAR,
            $this->consumoDetIdsDeFilas([$this->filaRefac]),
            $this->periodoRefac
        );
    }

    /**
     * Ejecuta el cambio de estado sobre los consumos indicados y guarda el
     * mensaje del repository para mostrarlo en la vista.
     *
     * @param  array<int, string>  $consumoDetIds
     */
    private function aplicarEstado(int $nuevoEstado, array $consumoDetIds, ?string $periodoRefac = null): void
    {
        if ($consumoDetIds === []) {
            $this->mensajeTipo = 'warning';
            $this->mensaje = 'No hay consumos del sistema asociados a las filas seleccionadas.';

            return;
        }

        $resultado = app(RendicionRepository::class)->cambiarEstados(
            $consumoDetIds,
            $nuevoEstado,
            $periodoRefac,
            null,
            $nuevoEstado === self::ESTADO_REFACTURAR
        );

        $this->mensajeTipo = $resultado['success'] ? 'success' : 'danger';
        $this->mensaje = $resultado['mensaje'];

        if ($resultado['success']) {
            $this->seleccionados = [];
            $this->filaRefac = null;

            $this->dispatch('close-modal', modal: 'opaRefacturarModal');
        }
    }

    /**
     * Consumos del sistema asociados a las filas de la auditoria indicadas.
     * Las filas sin contraparte (sin_consumo, sin_parte, repetidas) no tienen
     * consumo_det_id y quedan afuera.
     *
     * @param  array<int, int|string>  $filas
     * @return array<int, string>
     */
    private function consumoDetIdsDeFilas(array $filas): array
    {
        if ($filas === []) {
            return [];
        }

        return $this->orden->detalles()
            ->whereIn('id', $filas)
            ->whereNotNull('consumo_det_id')
            ->pluck('consumo_det_id')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Ordena la tabla por la columna indicada. Si ya estaba ordenada por esa
     * columna, invierte el sentido.
     */
    public function ordenarPor(string $columna): void
    {
        if (! array_key_exists($columna, self::COLUMNAS_ORDEN)) {
            return;
        }

        if ($this->ordenPor === $columna) {
            $this->ordenDireccion = $this->ordenDireccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenPor = $columna;
            $this->ordenDireccion = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Vuelve al orden por defecto: primero las diferencias mas graves.
     */
    public function ordenPorDefecto(): void
    {
        $this->ordenPor = '';
        $this->ordenDireccion = 'asc';

        $this->resetPage();
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): View
    {
        $detalles = $this->detallesQuery()->paginate(self::POR_PAGINA);

        $resumenSeveridad = $this->orden->detalles()
            ->select('severidad', DB::raw('COUNT(*) as total'))
            ->groupBy('severidad')
            ->pluck('total', 'severidad');

        $resumenEstados = $this->orden->detalles()
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $idsPantalla = $detalles->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $estados = Estado::get();

        return view('livewire.ordenes-pagos-audit.orden-pago-audit-detalle', [
            'detalles'          => $detalles,
            'resumenSeveridad'  => $resumenSeveridad,
            'resumenEstados'    => $resumenEstados,
            'iconosOrden'       => $this->iconosOrden(),
            'etiquetaOrden'     => self::ETIQUETAS_ORDEN[$this->ordenPor] ?? '',
            'colorGrupo'        => $this->colorGrupoPorFila($detalles->getCollection()),
            'todosSeleccionados' => $idsPantalla !== []
                && array_diff($idsPantalla, $this->seleccionados) === [],
            'filaRefacDetalle'  => $this->filaRefac !== null
                ? $this->orden->detalles()->find($this->filaRefac)
                : null,
            'periodos'          => $this->periodosDisponibles(),
            'estadosParte'      => $estados,
            'estadosParteNombres' => $estados->pluck('descripcion', 'id'),
        ]);
    }

    /**
     * Consulta base del detalle, con los filtros y el orden elegido.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    private function detallesQuery(): Builder|HasMany
    {
        $query = $this->orden->detalles()
            ->with('parte', 'consumoDet')
            ->when($this->busqueda !== '', function ($query) {
                $busqueda = '%'.$this->busqueda.'%';

                $query->where(function ($q) use ($busqueda) {
                    $q->where('atencion', 'like', $busqueda)
                        ->orWhere('codigo_archivo', 'like', $busqueda)
                        ->orWhere('codigo_sistema', 'like', $busqueda)
                        ->orWhere('nivel_sistema', 'like', $busqueda)
                        ->orWhere('descripcion_archivo', 'like', $busqueda)
                        ->orWhere('descripcion_sistema', 'like', $busqueda)
                        ->orWhere('obra_social_archivo', 'like', $busqueda);
                });
            })
            ->when($this->filtroEstado !== '', fn ($query) => $query->where('estado', $this->filtroEstado))
            ->when($this->filtroSeveridad !== '', fn ($query) => $query->where('severidad', $this->filtroSeveridad))
            ->when($this->soloDiferencias, fn ($query) => $query->where('estado', '!=', 'ok'));

        $this->aplicarOrden($query);

        return $query;
    }

    /**
     * Periodo actual en el formato que usa la tabla periodos (Y/m).
     */
    private function periodoActual(): string
    {
        return now()->format('Y/m');
    }

    /**
     * Periodos para el select, del mas nuevo al mas viejo. El periodo actual
     * se incluye siempre, aunque todavia no este cargado.
     *
     * @return array<int, string>
     */
    private function periodosDisponibles(): array
    {
        $periodos = Periodo::orderByDesc('nombre')->pluck('nombre')->all();

        if (! in_array($this->periodoActual(), $periodos, true)) {
            array_unshift($periodos, $this->periodoActual());
        }

        return $periodos;
    }

    /**
     * Color de cada fila segun el bloque de atencion al que pertenece.
     *
     * Las filas de una misma atencion comparten color y el color cambia al
     * pasar a la siguiente atencion, para separar los bloques a simple vista.
     *
     * @param  \Illuminate\Support\Collection<int, OrdenPagoAuditDet>  $detalles
     * @return array<int, string>
     */
    private function colorGrupoPorFila($detalles): array
    {
        $colores = ['azul', 'naranja'];

        $coloresFila = [];
        $atencionAnterior = null;
        $grupo = 0;
        $primero = true;

        foreach ($detalles as $detalle) {
            $atencion = (string) $detalle->atencion;

            if ($primero || $atencion !== $atencionAnterior) {
                $grupo++;
                $atencionAnterior = $atencion;
                $primero = false;
            }

            $coloresFila[$detalle->id] = $colores[($grupo - 1) % count($colores)];
        }

        return $coloresFila;
    }

    /**
     * Aplica el orden elegido sobre la consulta de detalles.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    private function aplicarOrden($query): void
    {
        $direccion = $this->ordenDireccion === 'desc' ? 'desc' : 'asc';

        if ($this->ordenPor === '' || ! isset(self::COLUMNAS_ORDEN[$this->ordenPor])) {
            // Orden por defecto: primero las diferencias mas graves.
            $query->orderByRaw("CASE severidad WHEN 'alta' THEN 1 WHEN 'media' THEN 2 WHEN 'baja' THEN 3 ELSE 4 END")
                ->orderBy('atencion')
                ->orderBy('codigo_archivo')
                ->orderBy('id');

            return;
        }

        // Obra social y atencion van siempre juntos: se agrupa por una y
        // dentro de cada grupo por la otra.
        if ($this->ordenPor === 'obra_social') {
            $this->ordenarPorObraSocial($query, $direccion);
            $query->orderBy('atencion', $direccion)
                ->orderBy('codigo_archivo');

            return;
        }

        if ($this->ordenPor === 'atencion') {
            $query->orderBy('atencion', $direccion);
            $this->ordenarPorObraSocial($query, $direccion);
            $query->orderBy('codigo_archivo');

            return;
        }

        $columna = self::COLUMNAS_ORDEN[$this->ordenPor];

        $query->orderByRaw($columna.' IS NULL ASC')
            ->orderBy($columna, $direccion)
            ->orderBy('id');
    }

    /**
     * Ordena por el cod_uom de la obra social, que es un codigo numerico, y
     * deja las filas sin obra social al final.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    private function ordenarPorObraSocial($query, string $direccion): void
    {
        // SIGNED y no INTEGER: MySQL no acepta "AS INTEGER".
        $query->orderByRaw('cod_uom_archivo IS NULL ASC')
            ->orderByRaw('CAST(cod_uom_archivo AS SIGNED) '.$direccion);
    }

    /**
     * Icono de orden para cada columna de la cabecera.
     *
     * @return array<string, string>
     */
    private function iconosOrden(): array
    {
        $iconos = [];

        foreach (array_keys(self::COLUMNAS_ORDEN) as $columna) {
            $iconos[$columna] = match (true) {
                $this->ordenPor !== $columna => 'fa-sort text-muted opacity-50',
                $this->ordenDireccion === 'asc' => 'fa-sort-up',
                default => 'fa-sort-down',
            };
        }

        return $iconos;
    }
}
