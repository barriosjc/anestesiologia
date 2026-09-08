<?php

namespace App\Livewire\Presupuestos;

use App\Models\Centro;
use App\Models\Profesional;
use App\Models\User;
use Livewire\Component;

class PresupuestoFiltro extends Component
{
    public ?int $centro_id = null;
    public ?string $nombre = null;
    public ?int $profesional_id = null;
    public ?int $usuario_id = null;
    public ?string $fecha_desde = null;
    public ?string $fecha_hasta = null;
    public ?string $numero = null;
    public ?string $estado = null;

    public function mount(): void
    {
        $this->centro_id = session('pc_centro_id');
        $this->nombre = session('pc_nombre');
        $this->profesional_id = session('pc_profesional_id');
        $this->usuario_id = session('pc_usuario_id');
        $this->fecha_desde = session('pc_fecha_desde');
        $this->fecha_hasta = session('pc_fecha_hasta');
        $this->numero = session('pc_numero');
        $this->estado = session('pc_estado');
    }

    protected function filtroKeys(): array
    {
        return ['centro_id', 'nombre', 'profesional_id', 'usuario_id', 'fecha_desde', 'fecha_hasta', 'numero', 'estado'];
    }

    public function aplicar(): void
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $filtros[$key] = $this->$key;
            session()->put('pc_' . $key, $this->$key);
        }

        $this->dispatch('filtros-aplicados', filtros: $filtros);
    }

    public function limpiar(): void
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $this->$key = null;
            $filtros[$key] = null;
            session()->forget('pc_' . $key);
        }

        $this->dispatch('filtros-aplicados', filtros: $filtros);
        $this->dispatch('filtro-limpiado');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.presupuestos.presupuesto-filtro', [
            'centros' => Centro::orderBy('nombre')->get(),
            'profesionales' => Profesional::orderBy('nombre')->get(),
            'usuarios' => User::get(),
            'estados' => PresupuestoTabla::ESTADOS,
        ]);
    }
}
