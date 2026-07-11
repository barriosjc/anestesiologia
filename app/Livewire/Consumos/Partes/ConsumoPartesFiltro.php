<?php

namespace App\Livewire\Consumos\Partes;

use App\Models\Centro;
use App\Models\Cobertura;
use App\Models\Estado;
use App\Models\Profesional;
use Livewire\Component;

class ConsumoPartesFiltro extends Component
{
    public $cobertura_id;
    public $centro_id;
    public $profesional_id;
    public $nombre;
    public $nro_parte;
    public $fec_desde;
    public $fec_hasta;
    public $estado_id = [];
    public $fec_desde_adm;
    public $fec_hasta_adm;

    public function mount()
    {
        $this->cobertura_id = session('a_cobertura_id');
        $this->centro_id = session('a_centro_id');
        $this->profesional_id = session('a_profesional_id');
        $this->nombre = session('a_nombre');
        $this->nro_parte = session('a_nro_parte');
        $this->fec_desde = session('a_fec_desde');
        $this->fec_hasta = session('a_fec_hasta');
        $this->estado_id = (array) session('a_estado_id', []);
        $this->fec_desde_adm = session('a_fec_desde_adm');
        $this->fec_hasta_adm = session('a_fec_hasta_adm');
    }

    protected function filtroKeys()
    {
        return [
            'cobertura_id', 'centro_id', 'profesional_id',
            'nombre', 'nro_parte', 'fec_desde', 'fec_hasta',
            'estado_id', 'fec_desde_adm', 'fec_hasta_adm',
        ];
    }

    public function aplicar()
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $filtros[$key] = $this->$key;
            session()->put('a_' . $key, $this->$key);
        }

        $this->dispatch('filtros-aplicados', filtros: $filtros);
    }

    public function limpiar()
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $this->$key = $key === 'estado_id' ? [] : null;
            $filtros[$key] = $this->$key;
            session()->forget('a_' . $key);
        }

        $this->dispatch('filtros-aplicados', filtros: $filtros);
        $this->dispatch('filtro-limpiado');
    }

    public function render()
    {
        return view('livewire.consumos.partes.consumo-partes-filtro', [
            'coberturas' => Cobertura::orderBy('nombre')->get(),
            'centros' => Centro::orderBy('nombre')->get(),
            'profesionales' => Profesional::orderBy('nombre')->get(),
            'estados' => Estado::get(),
        ]);
    }
}
