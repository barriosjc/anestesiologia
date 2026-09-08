<?php

namespace App\Livewire\Consumos\Rendiciones;

use App\Models\Centro;
use App\Models\Cobertura;
use App\Models\Estado;
use App\Models\Periodo;
use App\Models\Profesional;
use Livewire\Component;

class RendicionesFiltro extends Component
{
    public ?int $cobertura_id = null;
    public ?int $centro_id = null;
    public ?int $profesional_id = null;
    public ?string $nombre = null;
    public ?string $fec_desde = null;
    public ?string $fec_hasta = null;
    public array $estado_id = [];
    public ?string $periodo_gen = null;
    public ?string $nro_parte = null;

    public function mount(): void
    {
        $this->cobertura_id = session('c_cobertura_id');
        $this->centro_id = session('c_centro_id');
        $this->profesional_id = session('c_profesional_id');
        $this->nombre = session('c_nombre');
        $this->fec_desde = session('c_fec_desde');
        $this->fec_hasta = session('c_fec_hasta');
        $this->estado_id = (array) session('c_estado_id', []);
        $this->periodo_gen = session('c_periodo_gen');
        $this->nro_parte = session('c_nro_parte');
    }

    protected function filtroKeys(): array
    {
        return [
            'cobertura_id', 'centro_id', 'profesional_id', 'nombre',
            'fec_desde', 'fec_hasta', 'estado_id', 'periodo_gen', 'nro_parte',
        ];
    }

    public function aplicar(): void
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $filtros[$key] = $this->$key;
            session()->put('c_' . $key, $this->$key);
        }

        $this->dispatch('filtros-aplicados', filtros: $filtros);
    }

    public function limpiar(): void
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $this->$key = $key === 'estado_id' ? [] : null;
            $filtros[$key] = $this->$key;
            session()->forget('c_' . $key);
        }

        $this->dispatch('filtros-aplicados', filtros: $filtros);
        $this->dispatch('filtro-limpiado');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.consumos.rendiciones.rendiciones-filtro', [
            'coberturas' => Cobertura::orderBy('nombre')->get(),
            'centros' => Centro::orderBy('nombre')->get(),
            'profesionales' => Profesional::orderBy('nombre')->get(),
            'estados' => Estado::get(),
            'periodos' => Periodo::orderBy('nombre')->get(),
        ]);
    }
}
