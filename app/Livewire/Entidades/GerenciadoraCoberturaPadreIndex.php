<?php

namespace App\Livewire\Entidades;

use App\Models\Cobertura;
use App\Models\Gerenciadora;
use App\Models\GerenciadoraCoberturaNomPadre;
use App\Models\NomPadre;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class GerenciadoraCoberturaPadreIndex extends Component
{
    use WithPagination;

    public $modalId;
    public $gerenciadora_id;
    public $cobertura_id;
    public $nom_padre_id;

    public function abrirModalNuevo()
    {
        $this->reset(['modalId', 'gerenciadora_id', 'cobertura_id', 'nom_padre_id']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'gerenciadoraCoberturaPadreModal');
    }

    public function abrirModalEditar($id)
    {
        $registro = GerenciadoraCoberturaNomPadre::withTrashed()->findOrFail($id);

        $this->modalId = $registro->id;
        $this->gerenciadora_id = $registro->gerenciadora_id;
        $this->cobertura_id = $registro->cobertura_id;
        $this->nom_padre_id = $registro->nom_padre_id;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'gerenciadoraCoberturaPadreModal');
    }

    public function guardar()
    {
        $this->validate([
            'gerenciadora_id' => ['required', 'integer'],
            'cobertura_id'    => ['required', 'integer'],
            'nom_padre_id'    => [
                'required',
                'integer',
                Rule::unique('gerenciadoras_coberturas_nom_padres')
                    ->where(fn ($q) => $q
                        ->where('gerenciadora_id', $this->gerenciadora_id)
                        ->where('cobertura_id', $this->cobertura_id))
                    ->ignore($this->modalId),
            ],
        ], [
            'gerenciadora_id.required' => 'La gerenciadora es obligatoria.',
            'cobertura_id.required'    => 'La cobertura es obligatoria.',
            'nom_padre_id.required'    => 'El nomenclador padre es obligatorio.',
            'nom_padre_id.unique'      => 'Ya existe una relación con la misma Gerenciadora, Cobertura y Nomenclador Padre que se cargó anteriormente.',
        ]);

        $data = [
            'gerenciadora_id' => $this->gerenciadora_id,
            'cobertura_id'    => $this->cobertura_id,
            'nom_padre_id'    => $this->nom_padre_id,
        ];

        if ($this->modalId) {
            GerenciadoraCoberturaNomPadre::withTrashed()->findOrFail($this->modalId)->update($data);
        } else {
            GerenciadoraCoberturaNomPadre::create($data);
        }

        $this->dispatch('close-modal', modal: 'gerenciadoraCoberturaPadreModal');
        $this->reset(['modalId', 'gerenciadora_id', 'cobertura_id', 'nom_padre_id']);
        session()->flash('success', 'La operación se ha completado exitosamente.');
    }

    public function borrar($id)
    {
        $registro = GerenciadoraCoberturaNomPadre::withTrashed()->findOrFail($id);

        if ($registro->trashed()) {
            $registro->restore();
            session()->flash('success', 'Registro restaurado con éxito.');
        } else {
            $registro->delete();
            session()->flash('success', 'Registro enviado a la papelera.');
        }
    }

    public function paginationView()
    {
        return 'livewire::bootstrap';
    }

    public function render()
    {
        $gerenciadora_cobertura_padre = GerenciadoraCoberturaNomPadre::withTrashed()
            ->with(['gerenciadora', 'cobertura', 'nomPadre'])
            ->paginate(20);

        $gerenciadoras = Gerenciadora::orderBy('nombre')->get();
        $coberturas = Cobertura::orderBy('sigla')->get();
        $nom_padres = NomPadre::orderBy('nombre')->get();

        return view('livewire.entidades.gerenciadora-cobertura-padre-index', compact(
            'gerenciadora_cobertura_padre',
            'gerenciadoras',
            'coberturas',
            'nom_padres'
        ));
    }
}
