<?php

namespace App\Livewire\Nomencladores;

use App\Models\Nivel;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class NivelesIndex extends Component
{
    use WithPagination;

    public string $text = '';

    public ?int $modalId = null;
    public ?string $nivel = null;
    public ?string $cod_uom = null;
    public ?string $descripcion = null;

    public function mount(): void
    {
        $this->text = (string) request('text', '');
    }

    public function updatingText(): void
    {
        $this->resetPage();
    }

    public function abrirModalNuevo(): void
    {
        $this->reset(['modalId', 'nivel', 'cod_uom', 'descripcion']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'nivelModal');
    }

    public function abrirModalEditar(int $id): void
    {
        $nivel = Nivel::findOrFail($id);

        $this->modalId = $nivel->id;
        $this->nivel = $nivel->nivel;
        $this->cod_uom = $nivel->cod_uom;
        $this->descripcion = $nivel->descripcion;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'nivelModal');
    }

    public function guardar(): void
    {
        $this->validate([
            'nivel' => ['required', 'string', 'max:10'],
            'cod_uom' => [
                'required',
                'string',
                'max:10',
                Rule::unique('niveles', 'cod_uom')->ignore($this->modalId),
            ],
            'descripcion' => ['nullable', 'string', 'max:50'],
        ], [
            'nivel.required'    => 'El nivel interno es obligatorio.',
            'nivel.max'         => 'El nivel no puede superar los 10 caracteres.',
            'cod_uom.required'  => 'El código UOM es obligatorio.',
            'cod_uom.max'       => 'El código UOM no puede superar los 10 caracteres.',
            'cod_uom.unique'    => 'Ese código UOM ya tiene una equivalencia.',
            'descripcion.max'   => 'La descripción no puede superar los 50 caracteres.',
        ]);

        $data = [
            'nivel'       => $this->nivel,
            'cod_uom'     => $this->cod_uom,
            'descripcion' => $this->descripcion,
        ];

        if ($this->modalId) {
            Nivel::findOrFail($this->modalId)->update($data);
        } else {
            Nivel::create($data);
        }

        $this->dispatch('close-modal', modal: 'nivelModal');
        $this->reset(['modalId', 'nivel', 'cod_uom', 'descripcion']);
        session()->flash('success', 'Equivalencia guardada correctamente.');
    }

    public function borrar(int $id): void
    {
        Nivel::findOrFail($id)->delete();
        session()->flash('success', 'Equivalencia borrada correctamente.');
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $niveles = Nivel::query()
            ->when($this->text !== '', function ($query) {
                $texto = '%'.$this->text.'%';

                $query->where(function ($q) use ($texto) {
                    $q->where('nivel', 'like', $texto)
                        ->orWhere('cod_uom', 'like', $texto)
                        ->orWhere('descripcion', 'like', $texto);
                });
            })
            ->orderBy('cod_uom')
            ->paginate(25);

        return view('livewire.nomencladores.niveles-index', compact('niveles'));
    }
}
