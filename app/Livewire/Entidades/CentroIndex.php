<?php

namespace App\Livewire\Entidades;

use App\Models\Centro;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class CentroIndex extends Component
{
    use WithPagination;

    public ?int $modalId = null;
    public ?string $nombre = null;
    public ?string $cuit = null;
    public ?string $telefono = null;
    public ?string $contacto = null;

    public function abrirModalNuevo(): void
    {
        $this->reset(['modalId', 'nombre', 'cuit', 'telefono', 'contacto']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'centroModal');
    }

    public function abrirModalEditar(int $id): void
    {
        $centro = Centro::findOrFail($id);

        $this->modalId = $centro->id;
        $this->nombre = $centro->nombre;
        $this->cuit = $centro->cuit;
        $this->telefono = $centro->telefono;
        $this->contacto = $centro->contacto;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'centroModal');
    }

    public function guardar(): void
    {
        $this->validate([
            'nombre'   => ['required', 'string', 'max:200'],
            'cuit'     => ['required', 'string', 'max:15'],
            'telefono' => ['nullable', 'string', 'max:45'],
            'contacto' => ['nullable', 'string', 'max:45'],
        ], [
            'nombre.required'   => 'El nombre es obligatorio.',
            'nombre.max'        => 'El nombre no puede superar los 200 caracteres.',
            'cuit.required'     => 'El CUIT es obligatorio.',
            'cuit.max'          => 'El CUIT no puede superar los 15 caracteres.',
            'telefono.max'      => 'El teléfono no puede superar los 45 caracteres.',
            'contacto.max'      => 'El contacto no puede superar los 45 caracteres.',
        ]);

        $data = [
            'nombre'   => $this->nombre,
            'cuit'     => $this->cuit,
            'telefono' => $this->telefono,
            'contacto' => $this->contacto,
        ];

        if ($this->modalId) {
            Centro::findOrFail($this->modalId)->update($data);
        } else {
            Centro::create($data);
        }

        $this->dispatch('close-modal', modal: 'centroModal');
        $this->reset(['modalId', 'nombre', 'cuit', 'telefono', 'contacto']);
        session()->flash('success', 'Centro guardado correctamente.');
    }

    public function borrar(int $id): void
    {
        Centro::findOrFail($id)->delete();
        session()->flash('success', 'Centro borrado correctamente.');
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $centros = Centro::paginate(10);

        return view('livewire.entidades.centro-index', compact('centros'));
    }
}
