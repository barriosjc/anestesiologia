<?php

namespace App\Livewire\Presupuestos;

use App\Models\Centro;
use App\Models\Profesional;
use App\Models\User;
use Livewire\Component;

class PresupuestoFiltro extends Component
{
    public $centro_id;
    public $nombre;
    public $profesional_id;
    public $usuario_id;
    public $fecha_desde;
    public $fecha_hasta;
    public $estado;

    public function mount()
    {
        $this->centro_id = session('pc_centro_id');
        $this->nombre = session('pc_nombre');
        $this->profesional_id = session('pc_profesional_id');
        $this->usuario_id = session('pc_usuario_id');
        $this->fecha_desde = session('pc_fecha_desde');
        $this->fecha_hasta = session('pc_fecha_hasta');
        $this->estado = session('pc_estado');
    }

    protected function filtroKeys()
    {
        return ['centro_id', 'nombre', 'profesional_id', 'usuario_id', 'fecha_desde', 'fecha_hasta', 'estado'];
    }

    public function aplicar()
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $filtros[$key] = $this->$key;
            session()->put('pc_' . $key, $this->$key);
        }

        $this->dispatch('filtros-aplicados', filtros: $filtros);
    }

    public function limpiar()
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

    public function render()
    {
        return view('livewire.presupuestos.presupuesto-filtro', [
            'centros' => Centro::orderBy('nombre')->get(),
            'profesionales' => Profesional::orderBy('nombre')->get(),
            'usuarios' => User::get(),
            'estados' => PresupuestoTabla::ESTADOS,
        ]);
    }
}
