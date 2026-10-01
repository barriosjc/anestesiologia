<?php

namespace App\Livewire\TercerosAnestesia;

use App\Models\TerceroAnestesia;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class TercerosAnestesiaIndex extends Component
{
    use WithPagination;

    public string $busqueda = '';

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    /**
     * Navegación al formulario. Se hacen con wire:click + redirect y no con un
     * <a href> pelado para que la acción tenga ciclo de vida de Livewire: los
     * botones pueden enganchar wire:loading y mostrar spinner hasta que la
     * redirección se resuelve (wire:loading.target / wire:loading.attr).
     */
    public function irACrear(): void
    {
        $this->redirect(route('terceros_anestesia.create'));
    }

    public function irAEditar(int $id): void
    {
        $this->redirect(route('terceros_anestesia.edit', $id));
    }

    public function borrar(int $id): void
    {
        TerceroAnestesia::findOrFail($id)->delete();
        session()->flash('success', 'Registro eliminado correctamente.');
        $this->resetPage();
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $registros = TerceroAnestesia::query()
            ->with(['nomenclador', 'profesional', 'cobertura'])
            ->when($this->busqueda !== '', function ($q) {
                $q->where('paciente', 'like', '%' . $this->busqueda . '%');
            })
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(15);

        return view('livewire.terceros-anestesia.terceros-anestesia-index', [
            'registros' => $registros,
        ]);
    }
}