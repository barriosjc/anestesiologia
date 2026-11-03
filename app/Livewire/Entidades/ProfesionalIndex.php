<?php

namespace App\Livewire\Entidades;

use App\Models\Profesional;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class ProfesionalIndex extends Component
{
    use WithPagination;

    public ?int $modalId = null;
    public ?string $nombre = null;
    public ?string $cuit = null;
    public ?string $email = null;
    public ?string $telefono = null;

    public function abrirModalNuevo(): void
    {
        $this->reset(['modalId', 'nombre', 'cuit', 'email', 'telefono']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'profesionalModal');
    }

    public function abrirModalEditar(int $id): void
    {
        $profesional = Profesional::findOrFail($id);

        $this->modalId = $profesional->id;
        $this->nombre = $profesional->nombre;
        $this->cuit = $profesional->cuit;
        $this->email = $profesional->email;
        $this->telefono = $profesional->telefono;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'profesionalModal');
    }

    public function guardar(): void
    {
        $this->validate([
            'nombre'   => ['required', 'string', 'max:200'],
            'email'    => ['required', 'email'],
            'telefono' => ['required', 'string', 'max:45'],
            'cuit'     => ['required', 'string', 'max:11'],
        ], [
            'nombre.required'   => 'El nombre es obligatorio.',
            'nombre.max'        => 'El nombre no puede superar los 200 caracteres.',
            'email.required'    => 'El email es obligatorio.',
            'email.email'       => 'El email debe ser una dirección válida.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.max'      => 'El teléfono no puede superar los 45 caracteres.',
            'cuit.required'     => 'El CUIT es obligatorio.',
            'cuit.max'          => 'El CUIT no puede superar los 11 caracteres.',
        ]);

        $data = [
            'nombre'   => $this->nombre,
            'email'    => $this->email,
            'telefono' => $this->telefono,
            'cuit'     => $this->cuit,
        ];

        if ($this->modalId) {
            Profesional::findOrFail($this->modalId)->update($data);
        } else {
            $profesional = Profesional::create($data);
            $profesional->estado = 0;
            $profesional->save();
        }

        $this->dispatch('close-modal', modal: 'profesionalModal');
        $this->reset(['modalId', 'nombre', 'cuit', 'email', 'telefono']);
        session()->flash('success', 'Médico guardado correctamente.');
    }

    public function borrar(int $id): void
    {
        Profesional::findOrFail($id)->delete();
        session()->flash('success', 'Médico borrado correctamente.');
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $profesionales = Profesional::paginate(10);

        return view('livewire.entidades.profesional-index', compact('profesionales'));
    }
}
