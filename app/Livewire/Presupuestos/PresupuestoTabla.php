<?php

namespace App\Livewire\Presupuestos;

use App\Models\PresupuestoCab;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PresupuestoTabla extends Component
{
    use WithPagination;

    const ESTADOS = [
        'I' => ['texto' => 'Ingresado', 'clase' => 'bg-secondary'],
        'P' => ['texto' => 'Pagado', 'clase' => 'bg-success'],
        'C' => ['texto' => 'Cancelado', 'clase' => 'bg-danger'],
        'O' => ['texto' => 'Cobrado', 'clase' => 'bg-info'],
        'F' => ['texto' => 'Facturado', 'clase' => 'bg-primary'],
    ];

    public array $filtros = [];

    public function mount(): void
    {
        $this->filtros = [
            'centro_id' => session('pc_centro_id'),
            'nombre' => session('pc_nombre'),
            'profesional_id' => session('pc_profesional_id'),
            'usuario_id' => session('pc_usuario_id'),
            'fecha_desde' => session('pc_fecha_desde'),
            'fecha_hasta' => session('pc_fecha_hasta'),
            'numero' => session('pc_numero'),
            'estado' => session('pc_estado'),
        ];
    }

    #[On('filtros-aplicados')]
    public function aplicarFiltros(array $filtros): void
    {
        $this->filtros = $filtros;
        $this->resetPage();
    }

    public function destroy(int $id): void
    {
        $presupuesto = PresupuestoCab::findOrFail($id);
        $presupuesto->estado_anterior = $presupuesto->estado;
        $presupuesto->estado = 'C';
        $presupuesto->save();

        session()->flash('success', 'Presupuesto eliminado correctamente.');
    }

    public function restaurar(int $id): void
    {
        $presupuesto = PresupuestoCab::findOrFail($id);
        $presupuesto->estado = $presupuesto->estado_anterior;
        $presupuesto->save();

        session()->flash('success', 'Presupuesto restaurado correctamente.');
    }

    public function pagado(int $id): void
    {
        $presupuesto = PresupuestoCab::where('id', $id)->first();
        if (! $presupuesto) {
            session()->flash('error', 'Presupuesto cancelado, no es posible cambiar estado a Pagado.');
            return;
        }
        $presupuesto->estado = 'P';
        $presupuesto->save();

        session()->flash('success', 'Presupuesto marcado como pagado correctamente.');
    }

    public function generarPartes(int $id): void
    {
        $result = app(\App\Services\PresupuestoParteService::class)->generar($id);

        if ($result['success']) {
            session()->flash('success', $result['message']);

            return;
        }

        if (! empty($result['message'])) {
            $this->addError('generar', $result['message']);
        }
        foreach ($result['errors'] as $error) {
            $this->addError('generar', $error);
        }
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $query = DB::table('v_presupuestos_cab');

        if (!empty($this->filtros['centro_id'])) {
            $query->where('centro_id', $this->filtros['centro_id']);
        }
        if (!empty($this->filtros['nombre'])) {
            $query->where('nombre', 'like', '%' . $this->filtros['nombre'] . '%');
        }
        if (!empty($this->filtros['profesional_id'])) {
            $query->where('profesional_id', $this->filtros['profesional_id']);
        }
        if (!empty($this->filtros['usuario_id'])) {
            $query->where('usuario_id', $this->filtros['usuario_id']);
        }
        if (!empty($this->filtros['fecha_desde'])) {
            $query->whereDate('fecha', '>=', $this->filtros['fecha_desde']);
        }
        if (!empty($this->filtros['fecha_hasta'])) {
            $query->whereDate('fecha', '<=', $this->filtros['fecha_hasta']);
        }
        if (!empty($this->filtros['numero'])) {
            $query->where('id', $this->filtros['numero']);
        }
        if (!empty($this->filtros['estado'])) {
            $query->where('estado', $this->filtros['estado']);
        }

        $presupuestosCab = $query->orderBy('id', 'desc')->paginate(10);

        return view('livewire.presupuestos.presupuesto-tabla', [
            'presupuestosCab' => $presupuestosCab,
            'estados' => self::ESTADOS,
        ]);
    }
}
