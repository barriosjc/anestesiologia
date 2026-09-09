<?php

namespace App\Livewire\Entidades;

use App\Models\Documento;
<<<<<<< HEAD
use App\Models\Profesional_documento;
=======
use App\Models\ProfesionalDocumento;
>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.main')]
class ProfesionalDocumentacion extends Component
{
    use WithFileUploads;

    public int $profesionalId;
    public ?int $documento_id = null;
    public ?int $nro_hoja = null;
    public ?string $fecha_vcto = null;
    public ?\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $archivo = null;

    public function mount(int $id): void
    {
        $this->profesionalId = $id;
    }

    public function guardar(): void
    {
        $this->validate([
            'documento_id' => 'required|exists:documentos,id',
            'nro_hoja'     => 'required|integer',
            'fecha_vcto'   => [
                'nullable',
                'date',
                Rule::requiredIf(function () {
                    $documento = Documento::find($this->documento_id);

                    return $documento && strtoupper($documento->vencimiento) === 'S';
                }),
            ],
            'archivo' => ['required', 'file'],
        ], [
            'documento_id.required' => 'Debe seleccionar un tipo de documento.',
            'nro_hoja.required'     => 'Debe ingresar un número de hoja.',
            'nro_hoja.integer'      => 'El número de hoja debe ser numérico.',
            'fecha_vcto.required'   => 'La fecha de vencimiento es obligatoria para el tipo de documento ingresado.',
            'archivo.required'      => 'Debe seleccionar un archivo.',
        ]);

        try {
            $archivoNombre = $this->archivo->getClientOriginalName();

            $this->archivo->storeAs((string) $this->profesionalId, $archivoNombre, 'usuarios');

<<<<<<< HEAD
            Profesional_documento::create([
=======
            ProfesionalDocumento::create([
>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856
                'profesional_id' => $this->profesionalId,
                'documento_id'   => $this->documento_id,
                'nro_hoja'       => $this->nro_hoja,
                'path'           => $archivoNombre,
                'fecha_vcto'     => $this->fecha_vcto,
            ]);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo guardar la documentación. Verifique los datos e intente nuevamente.');

            return;
        }

        $this->reset(['documento_id', 'nro_hoja', 'fecha_vcto', 'archivo']);
        session()->flash('success', 'Documentación guardada correctamente.');
    }

    public function borrar(int $id): void
    {
        try {
<<<<<<< HEAD
            Profesional_documento::findOrFail($id)->delete();
=======
            ProfesionalDocumento::findOrFail($id)->delete();
>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo eliminar la documentación.');

            return;
        }

        session()->flash('success', 'Documentación eliminada correctamente.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $documentos = Documento::where('tipo', 'like', '%prof%')->get();
<<<<<<< HEAD
        $prof_docum = Profesional_documento::with('documento')
=======
        $prof_docum = ProfesionalDocumento::with('documento')
>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856
            ->where('profesional_id', $this->profesionalId)
            ->paginate(5);

        return view('livewire.entidades.profesional-documentacion', compact('prof_docum', 'documentos'));
    }
}
