<?php

namespace App\Livewire\Consumos\Partes;

use App\Models\Parte_cab;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ConsumoPartesTabla extends Component
{
    use WithPagination;

    public array $filtros = [];

    public function mount(): void
    {
        $this->filtros = [
            'cobertura_id' => session('a_cobertura_id'),
            'centro_id' => session('a_centro_id'),
            'profesional_id' => session('a_profesional_id'),
            'nombre' => session('a_nombre'),
            'nro_parte' => session('a_nro_parte'),
            'fec_desde' => session('a_fec_desde'),
            'fec_hasta' => session('a_fec_hasta'),
            'estado_id' => (array) session('a_estado_id', []),
            'fec_desde_adm' => session('a_fec_desde_adm'),
            'fec_hasta_adm' => session('a_fec_hasta_adm'),
        ];
    }

    #[On('filtros-aplicados')]
    public function aplicarFiltros(array $filtros): void
    {
        $this->filtros = $filtros;
        $this->resetPage();
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $query = Parte_cab::vParteCab();

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
            $query->where('paciente', 'like', '%' . $this->filtros['nombre'] . '%');
        }
        if (!empty($this->filtros['fec_desde'])) {
            $query->where('fec_prestacion_orig', '>=', $this->filtros['fec_desde']);
        }
        if (!empty($this->filtros['fec_hasta'])) {
            $query->where('fec_prestacion_orig', '<=', $this->filtros['fec_hasta']);
        }
        if (!empty($this->filtros['fec_desde_adm'])) {
            $query->where('created_at', '>=', $this->filtros['fec_desde_adm']);
        }
        if (!empty($this->filtros['fec_hasta_adm'])) {
            $query->where('created_at', '<=', $this->filtros['fec_hasta_adm']);
        }
        if (!empty($this->filtros['nro_parte'])) {
            $query->where('id', $this->filtros['nro_parte']);
        }

        $partes = $query->orderBy('created_at', 'asc')->paginate();

        return view('livewire.consumos.partes.consumo-partes-tabla', compact('partes'));
    }
}
