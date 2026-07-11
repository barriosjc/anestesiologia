<?php

namespace App\Livewire\Partes;

use App\Models\Estado;
use App\Models\Parte_cab;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ParteTabla extends Component
{
    use WithPagination;

    public $filtros = [];

    public function mount()
    {
        $this->filtros = [
            'cobertura_id' => session('p_cobertura_id'),
            'centro_id' => session('p_centro_id'),
            'profesional_id' => session('p_profesional_id'),
            'user_id' => session('p_user_id'),
            'nombre' => session('p_nombre'),
            'nro_parte' => session('p_nro_parte'),
            'fec_desde' => session('p_fec_desde'),
            'fec_hasta' => session('p_fec_hasta'),
            'estado_id' => (array) session('p_estado_id', []),
            'fec_desde_adm' => session('p_fec_desde_adm'),
            'fec_hasta_adm' => session('p_fec_hasta_adm'),
        ];
    }

    #[On('filtros-aplicados')]
    public function aplicarFiltros($filtros)
    {
        $this->filtros = $filtros;
        $this->resetPage();
    }

    public function destroy($id)
    {
        $parte = Parte_cab::find($id);

        // si esta ingresado o observado no se puede borrar
        if (in_array($parte->estado, [1, 2])) {
            session()->flash('error', "No es posible borrar el parte {$parte->id} con el estado actual.");
            return;
        }

        $parte->delete();

        session()->flash('success', 'Parte borrado correctamente.');
    }

    public function paginationView()
    {
        return 'vendor.pagination.bootstrap-4';
    }

    public function render()
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
        if (!empty($this->filtros['user_id'])) {
            $query->where('user_id', '=', $this->filtros['user_id']);
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
            $query->where('created_at', '<=', Carbon::parse($this->filtros['fec_hasta_adm'])->addDay()->format('Y-m-d'));
        }
        if (!empty($this->filtros['nro_parte'])) {
            $query->where('id', '=', $this->filtros['nro_parte']);
        }

        $partes = $query->orderBy('created_at', 'asc')->paginate(10);

        $estados = Estado::get();

        return view('livewire.partes.parte-tabla', compact('partes', 'estados'));
    }
}
