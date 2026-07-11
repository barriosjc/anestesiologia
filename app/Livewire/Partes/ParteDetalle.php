<?php

namespace App\Livewire\Partes;

use App\Models\Documento;
use App\Models\Parte_cab;
use App\Models\Parte_det;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class ParteDetalle extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $parte_cab_id;
    public $observaciones;

    public $documento_id;
    public $nro_hoja;
    public $archivo;

    public function mount($id)
    {
        $this->parte_cab_id = $id;
        $this->observaciones = Parte_cab::find($id)->observaciones;
    }

    public function rules()
    {
        return [
            'documento_id' => 'required|exists:documentos,id',
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:5548',
            'nro_hoja' => 'required|integer',
        ];
    }

    public function store()
    {
        $this->validate();

        $archivoNombre = $this->archivo->getClientOriginalName();
        $this->archivo->storeAs($this->parte_cab_id, $archivoNombre, 'partes');

        $parteDet = new Parte_det();
        $parteDet->parte_cab_id = $this->parte_cab_id;
        $parteDet->documento_id = $this->documento_id;
        $parteDet->nro_hoja = $this->nro_hoja;
        $parteDet->path = $archivoNombre;
        $parteDet->save();

        $this->reset('documento_id', 'nro_hoja', 'archivo');

        session()->flash('success', 'Documento cargado correctamente.');
    }

    public function destroy($id)
    {
        $parteDet = Parte_det::find($id);
        $parte = Parte_cab::find($parteDet->parte_cab_id);

        if (!in_array($parte->estado_id, [1, 2])) {
            session()->flash('error', 'No es posible borrar la documentación con el estado actual del parte.');
            return;
        }

        $parteDet->delete();

        session()->flash('success', 'Detalle de Parte borrado correctamente.');
    }

    public function paginationView()
    {
        return 'vendor.pagination.bootstrap-4';
    }

    public function render()
    {
        $documentos = Documento::where('tipo', 'like', '%parte%')->get();
        $estados = \App\Models\Estado::get();
        $partes = Parte_det::with('documento')
            ->where('parte_cab_id', $this->parte_cab_id)
            ->paginate(5);

        return view('livewire.partes.parte-detalle', compact('documentos', 'estados', 'partes'));
    }
}
