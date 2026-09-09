<?php

namespace App\Livewire\Partes;

use App\Models\Documento;
use App\Models\ParteCab;
use App\Models\ParteDet;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class ParteDetalle extends Component
{
    use WithFileUploads;
    use WithPagination;

    public int $parte_cab_id;
    public ?string $observaciones = null;

    public ?int $documento_id = null;
    public ?int $nro_hoja = null;
    public ?\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $archivo = null;

    public ?int $estado_cambio_id = null;
    public ?string $estado_cambio_obs = null;
    public ?int $estado_cambio_estado = null;

    public function mount(int $id): void
    {
        $this->parte_cab_id = $id;
        $this->observaciones = ParteCab::find($id)->observaciones;
    }

    public function rules(): array
    {
        return [
            'documento_id' => 'required|exists:documentos,id',
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:5548',
            'nro_hoja' => 'required|integer',
        ];
    }

    public function store(): void
    {
        $this->validate();

        $archivoNombre = $this->archivo->getClientOriginalName();
        $this->archivo->storeAs($this->parte_cab_id, $archivoNombre, 'partes');

        $parteDet = new ParteDet();
        $parteDet->parte_cab_id = $this->parte_cab_id;
        $parteDet->documento_id = $this->documento_id;
        $parteDet->nro_hoja = $this->nro_hoja;
        $parteDet->path = $archivoNombre;
        $parteDet->save();

        $this->reset('documento_id', 'nro_hoja', 'archivo');

        session()->flash('success', 'Documento cargado correctamente.');
    }

    public function destroy(int $id): void
    {
        $parteDet = ParteDet::find($id);
        $parte = ParteCab::find($parteDet->parte_cab_id);

        if (!in_array($parte->estado_id, [1, 2])) {
            session()->flash('error', 'No es posible borrar la documentación con el estado actual del parte.');
            return;
        }

        $parteDet->delete();

        session()->flash('success', 'Detalle de Parte borrado correctamente.');
    }

    public function abrirEstadoModal(int $id, ?string $observaciones = null): void
    {
        $this->estado_cambio_id = $id;
        $this->estado_cambio_obs = $observaciones;
        $this->estado_cambio_estado = null;
        $this->dispatch('open-modal', modal: 'valorModal');
    }

    public function cambiarEstado(): void
    {
        $this->validate([
            'estado_cambio_estado' => 'required',
        ], [
            'estado_cambio_estado.required' => '¡Atención! La selección del estado es obligatoria.',
        ]);

        $parte = ParteCab::findOrFail($this->estado_cambio_id);
        $parte->observaciones = strip_tags((string) $this->estado_cambio_obs);
        $parte->estado_id = $this->estado_cambio_estado;
        $parte->save();

        $this->reset('estado_cambio_id', 'estado_cambio_obs', 'estado_cambio_estado');
        $this->dispatch('close-modal', modal: 'valorModal');
        session()->flash('success', 'Estado cambiado exitosamente.');
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $documentos = Documento::where('tipo', 'like', '%parte%')->get();
        $estados = \App\Models\Estado::get();
        $partes = ParteDet::with('documento')
            ->where('parte_cab_id', $this->parte_cab_id)
            ->paginate(5);

        return view('livewire.partes.parte-detalle', compact('documentos', 'estados', 'partes'));
    }
}
