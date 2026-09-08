<?php

namespace App\Livewire\Entidades;

use App\Models\Cobertura;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class CoberturaIndex extends Component
{
    public ?string $text = null;

    public ?int $modalId = null;
    public ?string $nombre = null;
    public ?string $sigla = null;
    public ?string $cuit = null;
    public ?int $edad_desde = null;
    public ?int $edad_hasta = null;
    public ?int $porcentaje_adic = null;

    public function abrirModalNuevo(): void
    {
        $this->reset(['modalId', 'nombre', 'sigla', 'cuit', 'edad_desde', 'edad_hasta', 'porcentaje_adic']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'coberturaModal');
    }

    public function abrirModalEditar(int $id): void
    {
        $cobertura = Cobertura::findOrFail($id);

        $this->modalId = $cobertura->id;
        $this->nombre = $cobertura->nombre;
        $this->sigla = $cobertura->sigla;
        $this->cuit = $cobertura->cuit;
        $this->edad_desde = $cobertura->edad_desde;
        $this->edad_hasta = $cobertura->edad_hasta;
        $this->porcentaje_adic = $cobertura->porcentaje_adic;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'coberturaModal');
    }

    public function guardar(): void
    {
        $this->validate([
            'nombre'          => ['required', 'string', 'max:200'],
            'cuit'            => ['required', 'string', 'max:15'],
            'sigla'           => ['required', 'string', 'max:45'],
            'edad_desde'      => ['nullable', 'integer'],
            'edad_hasta'      => ['nullable', 'integer'],
            'porcentaje_adic' => ['nullable', 'integer'],
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.max'           => 'El nombre no puede superar los 200 caracteres.',
            'cuit.required'        => 'El CUIT es obligatorio.',
            'cuit.max'             => 'El CUIT no puede superar los 15 caracteres.',
            'sigla.required'       => 'La sigla es obligatoria.',
            'sigla.max'            => 'La sigla no puede superar los 45 caracteres.',
            'edad_desde.integer'   => 'La edad desde debe ser un número entero.',
            'edad_hasta.integer'   => 'La edad hasta debe ser un número entero.',
            'porcentaje_adic.integer' => 'El porcentaje adicional debe ser un número entero.',
        ]);

        $data = [
            'nombre'          => $this->nombre,
            'cuit'            => $this->cuit,
            'sigla'           => $this->sigla,
            'edad_desde'      => $this->edad_desde,
            'edad_hasta'      => $this->edad_hasta,
            'porcentaje_adic' => $this->porcentaje_adic,
        ];

        if ($this->modalId) {
            Cobertura::findOrFail($this->modalId)->update($data);
        } else {
            Cobertura::create($data);
        }

        $this->dispatch('close-modal', modal: 'coberturaModal');
        $this->reset(['modalId', 'nombre', 'sigla', 'cuit', 'edad_desde', 'edad_hasta', 'porcentaje_adic']);
        session()->flash('success', 'Cobertura guardada correctamente.');
    }

    public function borrar(int $id): void
    {
        Cobertura::findOrFail($id)->delete();
        session()->flash('success', 'Cobertura borrada correctamente.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $coberturas = Cobertura::when($this->text, function ($query) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->text}%")
                    ->orWhere('sigla', 'like', "%{$this->text}%");
            });
        })
            ->orderBy('nombre')
            ->get();

        return view('livewire.entidades.cobertura-index', compact('coberturas'));
    }
}
