<?php

namespace App\Livewire\DocumentosLlamaparse;

use App\Enums\EstadoDocumentoLlamaparse;
use App\Exceptions\LlamaParseException;
use App\Models\DocumentoLlamaparse;
use App\Services\LlamaParse\LlamaParseService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

#[Layout('layouts.main')]
class DocumentoLlamaparseIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public ?TemporaryUploadedFile $archivo = null;

    public string $estado = '';

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    public function subir(): void
    {
        $maxKb = max(1, (int) config('llamaparse.max_file_mb', 100) * 1024);

        $this->validate([
            'archivo' => ['required', 'file', 'mimes:pdf', 'max:'.$maxKb],
        ], [
            'archivo.required' => 'Debe seleccionar un archivo.',
            'archivo.file' => 'El archivo seleccionado no es valido.',
            'archivo.mimes' => 'El archivo debe ser PDF.',
            'archivo.max' => 'El archivo supera el tamano maximo permitido.',
        ]);

        try {
            $service = app(LlamaParseService::class);
            $documento = $service->registrar($this->archivo, auth()->id());
            $service->iniciar($documento);
        } catch (LlamaParseException $e) {
            $this->addError('archivo', $e->getMessage());

            return;
        }

        $this->reset('archivo');
        $this->resetValidation();

        session()->flash('success', 'PDF enviado a LlamaParse. El documento se actualizara automaticamente.');

        $this->redirectRoute('documentos_llamaparse.ver', $documento);
    }

    public function consultarTodos(): void
    {
        $pendientes = DocumentoLlamaparse::query()
            ->whereIn('estado', [EstadoDocumentoLlamaparse::Pendiente, EstadoDocumentoLlamaparse::Procesando])
            ->orderBy('id')
            ->limit(5)
            ->get();

        foreach ($pendientes as $documento) {
            try {
                app(LlamaParseService::class)->consultar($documento);
            } catch (Throwable $e) {
                report($e);
            }
        }
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): View
    {
        $documentos = DocumentoLlamaparse::query()
            ->with('usuario')
            ->when($this->estado !== '', fn ($query) => $query->where('estado', $this->estado))
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.documentos-llamaparse.documento-llamaparse-index', [
            'documentos' => $documentos,
            'estados' => EstadoDocumentoLlamaparse::cases(),
            'disponible' => app(LlamaParseService::class)->configurado(),
            'hayEnProceso' => DocumentoLlamaparse::query()
                ->whereIn('estado', [EstadoDocumentoLlamaparse::Pendiente, EstadoDocumentoLlamaparse::Procesando])
                ->exists(),
        ]);
    }
}
