<?php

namespace App\Livewire\DocumentosLlamaparse;

use App\Enums\EstadoDocumentoLlamaparse;
use App\Exceptions\LlamaParseException;
use App\Models\DocumentoLlamaparse;
use App\Models\OrdenPagoAuditCab;
use App\Services\LlamaParse\LlamaParseService;
use App\Services\OrdenPagoAuditCompareService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class DocumentoLlamaparseDetalle extends Component
{
    public DocumentoLlamaparse $documento;

    /** Errores de la ultima auditoria lanzada desde esta pantalla. */
    public array $erroresAuditoria = [];

    /** Datos del archivo sin codigo equivalente (la auditoria se cancela). */
    public array $inconsistenciasAuditoria = [];

    public function mount(DocumentoLlamaparse $documento): void
    {
        $this->documento = $documento;
    }

    public function refrescar(): void
    {
        try {
            app(LlamaParseService::class)->consultar($this->documento);
        } catch (\Throwable $e) {
            report($e);
        }

        $this->documento->refresh();
    }

    /**
     * Validacion estricta: si el archivo tiene datos sin equivalencia, la
     * auditoria se cancela y se muestran los datos para codificarlos.
     */
    public function auditar(): void
    {
        if ($this->documento->json === null) {
            $this->addError('documento', 'El documento todavía no tiene JSON para auditar.');

            return;
        }

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($this->documento);

        $this->erroresAuditoria = $resultado['errors'];
        $this->inconsistenciasAuditoria = $resultado['inconsistencias'] ?? [];

        if (! $resultado['success'] || $resultado['cabecera_id'] === null) {
            session()->flash('error', $resultado['message']);

            return;
        }

        session()->flash('success', $resultado['message']);

        $this->redirectRoute('ordenes_pagos_audit.detalle', ['orden' => $resultado['cabecera_id']]);
    }

    /**
     * Abre la tabla de relaciones y diferencias. Si la auditoria todavia no
     * existe la genera sin validacion estricta, para que las filas del archivo
     * que no estan en el sistema y las del sistema que faltan en el archivo se
     * vean igual, marcadas como tales.
     */
    public function verRelaciones(): void
    {
        if ($this->documento->json === null) {
            $this->addError('documento', 'El documento todavía no tiene JSON para relacionar.');

            return;
        }

        $auditoria = $this->auditoriaExistente();

        if ($auditoria !== null) {
            $this->redirectRoute('ordenes_pagos_audit.detalle', ['orden' => $auditoria->id]);

            return;
        }

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($this->documento, false);

        $this->erroresAuditoria = $resultado['errors'];
        $this->inconsistenciasAuditoria = $resultado['inconsistencias'] ?? [];

        if (! $resultado['success'] || $resultado['cabecera_id'] === null) {
            session()->flash('error', $resultado['message']);

            return;
        }

        session()->flash('success', 'Relaciones generadas. Las filas sin contraparte quedaron marcadas.');

        $this->redirectRoute('ordenes_pagos_audit.detalle', ['orden' => $resultado['cabecera_id']]);
    }

    private function auditoriaExistente(): ?OrdenPagoAuditCab
    {
        if (! Schema::hasTable('ordenes_pagos_audit_cab')
            || ! Schema::hasColumn('ordenes_pagos_audit_cab', 'documento_llamaparse_id')) {
            return null;
        }

        return OrdenPagoAuditCab::query()
            ->where('documento_llamaparse_id', $this->documento->id)
            ->first();
    }

    public function reintentarConsulta(): void
    {
        if (blank($this->documento->job_id)) {
            $this->reprocesar();

            return;
        }

        $this->documento->update([
            'estado' => EstadoDocumentoLlamaparse::Procesando,
            'progreso_mensaje' => 'Reintentando consulta a LlamaParse',
            'error' => null,
        ]);

        $this->refrescar();
        $this->resetErrorBag('documento');

        if ($this->documento->estado === EstadoDocumentoLlamaparse::Procesado) {
            session()->flash('success', 'Documento procesado correctamente.');

            return;
        }

        if ($this->documento->estado === EstadoDocumentoLlamaparse::Error) {
            session()->flash('error', 'LlamaParse informó un error al procesar el documento.');

            return;
        }

        session()->flash('info', 'Se reintentó la consulta. Si el trabajo sigue en proceso, la pantalla se actualizará automáticamente.');
    }

    public function reprocesar(): void
    {
        try {
            app(LlamaParseService::class)->reprocesar($this->documento);
        } catch (LlamaParseException $e) {
            $this->addError('documento', $e->getMessage());

            return;
        }

        $this->documento->refresh();
        $this->resetErrorBag('documento');

        session()->flash('success', 'Documento enviado a reprocesar.');
    }

    public function borrar(): void
    {
        app(LlamaParseService::class)->eliminar($this->documento);

        session()->flash('success', 'Documento LlamaParse eliminado.');

        $this->redirectRoute('documentos_llamaparse.index');
    }

    public function render(): View
    {
        $json = $this->documento->json;
        $auditoria = $this->auditoriaExistente();

        return view('livewire.documentos-llamaparse.documento-llamaparse-detalle', [
            'json' => $json,
            'auditoria' => $auditoria,
            'jsonPretty' => $json !== null
                ? (string) json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : '',
            'markdownPretty' => $this->documento->markdown !== null
                ? (string) json_encode($this->documento->markdown, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : '',
            'itemsPretty' => $this->documento->items !== null
                ? (string) json_encode($this->documento->items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : '',
        ]);
    }
}
