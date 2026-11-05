<?php

namespace App\Livewire\OrdenesPagosAudit;

use App\Enums\EstadoDocumentoLlamaparse;
use App\Http\Controllers\OrdenesPagosAudit\OrdenPagoAuditUploadController;
use App\Models\DocumentoLlamaparse;
use App\Models\OrdenPagoAuditCab;
use App\Services\OrdenPagoAuditCompareService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class OrdenPagoAuditIndex extends Component
{
    use WithPagination;

    /** Busqueda sobre nro_op y nombre del profesional. */
    public string $busqueda = '';

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
     *
     * La subida en si se hace por XHR contra OrdenPagoAuditUploadController
     * (ver public/js/upload-directo.js), sin la subida temporal de Livewire.
     */
    public function abrirModalSubir(): void
    {
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'opAuditUploadModal');
    }

    /**
     * Refresca el listado despues de una importacion hecha por la ruta de subida.
     */
    #[On('orden-pago-audit-importada')]
    public function refrescarListado(): void
    {
        $this->resetPage();
    }

    /**
     * Limite real de subida (aplicacion y php.ini).
     */
    #[Computed]
    public function maxArchivoMb(): int
    {
        return OrdenPagoAuditUploadController::maxMb();
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
