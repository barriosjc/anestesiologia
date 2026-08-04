<?php

namespace App\Livewire\Nomencladores;

use App\Models\NomPadre;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class NomPadreIndex extends Component
{
    use WithPagination;

    public ?string $tipo = null;
    public ?int $modalId = null;
    public ?string $nombre = null;

    public function mount(): void
    {
        $tipo = request('tipo') ?? session('ses_nom_tipo');
        if (empty($tipo)) {
            $tipo = 1;
        }
        $this->tipo = $tipo;
        session(['ses_nom_tipo' => $tipo]);
    }

    public function abrirModalNuevo(): void
    {
        $this->reset(['modalId', 'nombre']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'nomPadreModal');
    }

    public function guardar(): void
    {
        $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo'   => ['required', 'string', 'max:2'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 255 caracteres.',
            'tipo.required'   => 'El tipo es obligatorio.',
        ]);

        try {
            NomPadre::create([
                'nombre' => $this->nombre,
                'tipo'   => $this->tipo,
            ]);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo guardar el nomenclador. Verifique los datos e intente nuevamente.');

            return;
        }

        $this->dispatch('close-modal', modal: 'nomPadreModal');
        $this->reset(['modalId', 'nombre']);
        session()->flash('success', 'Nomenclador guardado correctamente.');
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $nomencladores = NomPadre::where('tipo', $this->tipo)->paginate(10);

        return view('livewire.nomencladores.nom-padre-index', compact('nomencladores'));
    }
}
