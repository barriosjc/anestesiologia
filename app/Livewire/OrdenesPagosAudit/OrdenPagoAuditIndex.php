<?php

namespace App\Livewire\OrdenesPagosAudit;

use App\Enums\EstadoDocumentoLlamaparse;
use App\Exceptions\LlamaParseException;
use App\Models\DocumentoLlamaparse;
use App\Models\OrdenPagoAuditCab;
use App\Services\LlamaParse\LlamaParseService;
use App\Services\OrdenPagoAuditCompareService;
use App\Services\OrdenPagoAuditImportService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class OrdenPagoAuditIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    /** Busqueda sobre nro_op y nombre del profesional. */
    public string $busqueda = '';

    /** Archivo a importar (CSV / XLSX) o procesar (PDF). */
    public ?TemporaryUploadedFile $archivo = null;

    /** Errores de la ultima importacion. */
    public array $erroresImportacion = [];

    /** Documento procesado que se va a auditar contra la base. */
    public ?int $documentoAuditarId = null;

    /** Filtro del selector de documentos procesados. */
    public string $busquedaDocumento = '';

    /** Error de la ultima auditoria. */
    public array $erroresAuditoria = [];

    /** Datos del archivo sin codigo equivalente (la auditoria se cancela). */
    public array $inconsistenciasAuditoria = [];

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function abrirModalAuditar(): void
    {
        $this->reset(['documentoAuditarId', 'busquedaDocumento', 'erroresAuditoria', 'inconsistenciasAuditoria']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'opAuditAuditarModal');
    }

    public function auditar(): void
    {
        $this->validate([
            'documentoAuditarId' => ['required', 'integer', 'exists:documentos_llamaparse,id'],
        ], [
            'documentoAuditarId.required' => 'Debe seleccionar un documento procesado.',
            'documentoAuditarId.exists'   => 'El documento seleccionado no existe.',
        ]);

        $documento = DocumentoLlamaparse::findOrFail($this->documentoAuditarId);
        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->erroresAuditoria = $resultado['errors'];
        $this->inconsistenciasAuditoria = $resultado['inconsistencias'] ?? [];

        if (! $resultado['success'] || $resultado['cabecera_id'] === null) {
            session()->flash('error', $resultado['message']);

            return;
        }

        $this->dispatch('close-modal', modal: 'opAuditAuditarModal');
        session()->flash('success', $resultado['message']);

        $this->redirectRoute('ordenes_pagos_audit.detalle', ['orden' => $resultado['cabecera_id']]);
    }

    /**
     * Abre el modal de carga de archivo.
     */
    public function abrirModalSubir(): void
    {
        $this->reset(['archivo', 'erroresImportacion']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'opAuditUploadModal');
    }

    /**
     * Importa CSV/XLSX o envia el PDF a LlamaParse.
     */
    public function importar(): void
    {
        $maxKb = max(1, (int) config('llamaparse.max_file_mb', 100) * 1024);

        $this->validate([
            'archivo' => ['required', 'file', 'max:'.$maxKb],
        ], [
            'archivo.required' => 'Debe seleccionar un archivo.',
            'archivo.file'     => 'El archivo seleccionado no es valido.',
            'archivo.max'      => 'El archivo supera el tamano maximo permitido.',
        ]);

        $extension = strtolower($this->archivo->getClientOriginalExtension());

        if ($extension === 'pdf') {
            $this->procesarPdfConLlamaparse();

            return;
        }

        if (! in_array($extension, ['csv', 'txt', 'xls', 'xlsx'], true)) {
            $this->addError('archivo', 'El archivo debe ser CSV, TXT, XLS, XLSX o PDF.');

            return;
        }

        $resultado = app(OrdenPagoAuditImportService::class)->importar($this->archivo->getRealPath());

        $this->erroresImportacion = $resultado['errors'];

        if (! $resultado['success']) {
            session()->flash('error', $resultado['message']);

            return;
        }

        $this->reset(['archivo', 'erroresImportacion']);
        $this->resetValidation();
        $this->resetPage();

        $this->dispatch('close-modal', modal: 'opAuditUploadModal');

        session()->flash('success', $resultado['message']);
    }

    private function procesarPdfConLlamaparse(): void
    {
        try {
            $service = app(LlamaParseService::class);
            $documento = $service->registrar($this->archivo, auth()->id());
            $service->iniciar($documento);
        } catch (LlamaParseException $e) {
            $this->addError('archivo', $e->getMessage());

            return;
        }

        $this->reset(['archivo', 'erroresImportacion']);
        $this->resetValidation();
        $this->dispatch('close-modal', modal: 'opAuditUploadModal');
        $this->resetPage();

        $this->js('window.open('.json_encode(route('documentos_llamaparse.ver', $documento)).', "_blank")');

        session()->flash('success', 'PDF enviado a LlamaParse. Se abrio el detalle en otra pestana.');
    }

    /**
     * Elimina una orden de pago del listado.
     */
    public function borrar(int $id): void
    {
        OrdenPagoAuditCab::findOrFail($id)->delete();
        session()->flash('success', 'Orden de pago eliminada correctamente.');
        $this->resetPage();
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $registros = OrdenPagoAuditCab::query()
            ->with('profesional')
            ->when($this->busqueda !== '', function ($query) {
                $busqueda = '%' . $this->busqueda . '%';

                $query->where(function ($q) use ($busqueda) {
                    $q->where('nro_op', 'like', $busqueda)
                        ->orWhereHas('profesional', function ($qp) use ($busqueda) {
                            $qp->where('nombre', 'like', $busqueda);
                        });
                });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15);

        $documentos = DocumentoLlamaparse::query()
            ->where('estado', EstadoDocumentoLlamaparse::Procesado->value)
            ->whereNotNull('json')
            ->when($this->busquedaDocumento !== '', function ($query) {
                $busqueda = '%'.$this->busquedaDocumento.'%';

                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombre_original', 'like', $busqueda)
                        ->orWhere('uuid', 'like', $busqueda);
                });
            })
            ->orderByDesc('procesado_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'nombre_original', 'procesado_at', 'uuid']);

        return view('livewire.ordenes-pagos-audit.orden-pago-audit-index', [
            'registros'  => $registros,
            'documentos' => $documentos,
        ]);
    }
}
