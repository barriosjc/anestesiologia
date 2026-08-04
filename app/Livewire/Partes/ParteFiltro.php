<?php

namespace App\Livewire\Partes;

use App\Models\Centro;
use App\Models\Cobertura;
use App\Models\Estado;
use App\Models\Profesional;
use App\Models\User;
use Livewire\Component;

class ParteFiltro extends Component
{
    public ?int $cobertura_id = null;
    public ?int $centro_id = null;
    public ?int $profesional_id = null;
    public ?int $user_id = null;
    public ?string $nombre = null;
    public ?string $nro_parte = null;
    public ?string $fec_desde = null;
    public ?string $fec_hasta = null;
    public array $estado_id = [];
    public ?string $fec_desde_adm = null;
    public ?string $fec_hasta_adm = null;

    public function mount(): void
    {
        $this->cobertura_id = session('p_cobertura_id');
        $this->centro_id = session('p_centro_id');
        $this->profesional_id = session('p_profesional_id');
        $this->user_id = session('p_user_id');
        $this->nombre = session('p_nombre');
        $this->nro_parte = session('p_nro_parte');
        $this->fec_desde = session('p_fec_desde');
        $this->fec_hasta = session('p_fec_hasta');
        $this->estado_id = (array) session('p_estado_id', []);
        $this->fec_desde_adm = session('p_fec_desde_adm');
        $this->fec_hasta_adm = session('p_fec_hasta_adm');
    }

    protected function filtroKeys(): array
    {
        return [
            'cobertura_id', 'centro_id', 'profesional_id', 'user_id',
            'nombre', 'nro_parte', 'fec_desde', 'fec_hasta',
            'estado_id', 'fec_desde_adm', 'fec_hasta_adm',
        ];
    }

    public function aplicar(): void
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $filtros[$key] = $this->$key;
            session()->put('p_' . $key, $this->$key);
        }

        $this->dispatch('filtros-aplicados', filtros: $filtros);
    }

    public function limpiar(): void
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $this->$key = $key === 'estado_id' ? [] : null;
            $filtros[$key] = $this->$key;
            session()->forget('p_' . $key);
        }

        $this->dispatch('filtros-aplicados', filtros: $filtros);
        $this->dispatch('filtro-limpiado');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.partes.parte-filtro', [
            'coberturas' => Cobertura::orderBy('nombre')->get(),
            'centros' => Centro::orderBy('nombre')->get(),
            'profesionales' => Profesional::orderBy('nombre')->get(),
            'estados' => Estado::get(),
            'users' => User::get(),
        ]);
    }
}
