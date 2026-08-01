<?php

namespace App\Livewire\Entidades;

use App\Models\Parametro;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class ParametroIndex extends Component
{
    use WithPagination;

    public $modalId;
    public $nombre;
    public $valor;

    public function abrirModalNuevo()
    {
        $this->reset(['modalId', 'nombre', 'valor']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'parametroModal');
    }

    public function abrirModalEditar($id)
    {
        $parametro = Parametro::findOrFail($id);

        $this->modalId = $parametro->id;
        $this->nombre = $parametro->nombre;
        $this->valor = $parametro->valor;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'parametroModal');
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'valor'  => ['required', 'string', 'max:50'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 50 caracteres.',
            'valor.required'  => 'El valor es obligatorio.',
            'valor.max'       => 'El valor no puede superar los 50 caracteres.',
        ]);

        $data = ['nombre' => $this->nombre, 'valor' => $this->valor];

        if ($this->modalId) {
            Parametro::findOrFail($this->modalId)->update($data);
        } else {
            Parametro::create($data);
        }

        $this->dispatch('close-modal', modal: 'parametroModal');
        $this->reset(['modalId', 'nombre', 'valor']);
        session()->flash('success', 'Parámetro guardado correctamente.');
    }

    public function borrar($id)
    {
        Parametro::findOrFail($id)->delete();
        session()->flash('success', 'Parámetro borrado correctamente.');
    }

    public function paginationView()
    {
        return 'livewire::bootstrap';
    }

    public function render()
    {
        $parametros = Parametro::paginate(10);

        return view('livewire.entidades.parametro-index', compact('parametros'));
    }
}
