<?php

namespace App\Livewire\Nomencladores;

use App\Models\Nomenclador;
use App\Models\NomPadre;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class NomencladorIndex extends Component
{
    use WithPagination;

    public $nomPadreId;
    public $text;

    public $modalId;
    public $codigo;
    public $nivel;
    public $descripcion;

    public function mount($nom_padre)
    {
        $this->nomPadreId = $nom_padre;
        $this->text = request('text');
        session(['ses_nom_padre_id' => $nom_padre]);
    }

    public function filtrar()
    {
        $this->resetPage();
    }

    public function limpiar()
    {
        $this->reset(['text']);
        $this->resetPage();
    }

    public function abrirModalNuevo()
    {
        $this->reset(['modalId', 'codigo', 'nivel', 'descripcion']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'nomencladorModal');
    }

    public function abrirModalEditar($id)
    {
        $nomenclador = Nomenclador::withTrashed()->findOrFail($id);

        $this->modalId = $nomenclador->id;
        $this->codigo = $nomenclador->codigo;
        $this->nivel = $nomenclador->nivel;
        $this->descripcion = $nomenclador->descripcion;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'nomencladorModal');
    }

    public function guardar()
    {
        $this->validate([
            'codigo' => [
                'required',
                'string',
                Rule::unique('nomenclador')
                    ->where(fn ($q) => $q->where('nom_padre_id', $this->nomPadreId))
                    ->ignore($this->modalId),
            ],
            'nivel'       => ['required', 'string', 'max:5'],
            'descripcion' => ['required', 'string', 'max:200'],
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.max'      => 'El código no puede tener más de 10 caracteres.',
            'codigo.unique'   => 'Ya existe un código de nomenclador para este listado.',
            'nivel.required'  => 'El nivel es obligatorio.',
            'nivel.max'       => 'El nivel no puede tener más de 5 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 200 caracteres.',
        ]);

        $data = [
            'nom_padre_id' => $this->nomPadreId,
            'codigo'       => $this->codigo,
            'nivel'        => $this->nivel,
            'descripcion'  => $this->descripcion,
        ];

        try {
            if ($this->modalId) {
                Nomenclador::withTrashed()->findOrFail($this->modalId)->update($data);
            } else {
                Nomenclador::create($data);
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo guardar el nomenclador. Verifique los datos e intente nuevamente.');

            return;
        }

        $this->dispatch('close-modal', modal: 'nomencladorModal');
        $this->reset(['modalId', 'codigo', 'nivel', 'descripcion']);
        session()->flash('success', 'Registro guardado correctamente.');
    }

    public function borrar($id)
    {
        try {
            $nomenclador = Nomenclador::withTrashed()->findOrFail($id);
            $restaurando = $nomenclador->trashed();

            if ($restaurando) {
                $nomenclador->restore();
            } else {
                $nomenclador->delete();
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo completar la operación sobre el nomenclador.');

            return;
        }

        session()->flash('success', $restaurando ? 'Registro restaurado correctamente.' : 'Registro borrado correctamente.');
    }

    public function paginationView()
    {
        return 'livewire::bootstrap';
    }

    public function render()
    {
        $nomPadre = NomPadre::findOrFail($this->nomPadreId);

        $nomenclador = Nomenclador::withTrashed()
            ->where('nom_padre_id', $this->nomPadreId)
            ->when($this->text, function ($query) {
                $query->where(function ($q) {
                    $q->where('codigo', 'like', "%{$this->text}%")
                        ->orWhere('nivel', 'like', "%{$this->text}%")
                        ->orWhere('descripcion', 'like', "%{$this->text}%");
                });
            })
            ->orderBy('codigo')
            ->paginate();

        return view('livewire.nomencladores.nomenclador-index', compact('nomenclador', 'nomPadre'));
    }
}
