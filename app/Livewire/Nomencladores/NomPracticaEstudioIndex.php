<?php

namespace App\Livewire\Nomencladores;

use App\Models\NomPadre;
use App\Models\NomPracticasEstudio;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class NomPracticaEstudioIndex extends Component
{
    use WithPagination;

    public int $nomPadreId;
    public string $search = '';
    public ?int $modalId = null;
    public ?string $codigo = null;
    public ?string $nombre = null;

    public function mount(int $nom_padre): void
    {
        $this->nomPadreId = $nom_padre;
        session(['ses_nom_padre_id' => $nom_padre]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function abrirModalNuevo(): void
    {
        $this->reset(['modalId', 'codigo', 'nombre']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'nomPracticaModal');
    }

    public function abrirModalEditar(int $id): void
    {
        $nomPractica = NomPracticasEstudio::withTrashed()->findOrFail($id);

        $this->modalId = $nomPractica->id;
        $this->codigo = $nomPractica->codigo;
        $this->nombre = $nomPractica->nombre;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'nomPracticaModal');
    }

    public function guardar(): void
    {
        $this->validate([
            'codigo' => [
                'required',
                'string',
                Rule::unique('nom_practicas_estudios', 'codigo')
                    ->where('nom_padre_id', $this->nomPadreId)
                    ->ignore($this->modalId),
            ],
            'nombre' => ['required', 'string', 'max:255'],
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique'   => 'El código ingresado ya fue asignado a otra práctica o estudio, no se puede guardar o modificar. Por favor, ingrese otro.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 255 caracteres.',
        ]);

        $data = [
            'nom_padre_id' => $this->nomPadreId,
            'codigo'       => $this->codigo,
            'nombre'       => $this->nombre,
        ];

        try {
            if ($this->modalId) {
                NomPracticasEstudio::findOrFail($this->modalId)->update($data);
            } else {
                NomPracticasEstudio::create($data);
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo guardar la práctica o estudio. Verifique los datos e intente nuevamente.');

            return;
        }

        $this->dispatch('close-modal', modal: 'nomPracticaModal');
        $this->reset(['modalId', 'codigo', 'nombre']);
        session()->flash('success', 'Práctica o estudio creada correctamente.');
    }

    public function borrar(int $id): void
    {
        try {
            NomPracticasEstudio::findOrFail($id)->delete();
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo eliminar la práctica o estudio.');

            return;
        }

        session()->flash('success', 'Práctica o estudio eliminada correctamente.');
    }

    public function restaurar(int $id): void
    {
        try {
            NomPracticasEstudio::withTrashed()->findOrFail($id)->restore();
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo restaurar la práctica o estudio.');

            return;
        }

        session()->flash('success', 'Registro restaurado correctamente.');
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $nomPadre = NomPadre::findOrFail($this->nomPadreId);

        $nomPracticasEstudios = NomPracticasEstudio::withTrashed()
            ->where('nom_padre_id', $this->nomPadreId)
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('codigo', 'like', "%{$this->search}%")
                        ->orWhere('nombre', 'like', "%{$this->search}%");
                });
            })
            ->paginate(10);

        return view('livewire.nomencladores.nom-practica-estudio-index', compact('nomPadre', 'nomPracticasEstudios'));
    }
}
