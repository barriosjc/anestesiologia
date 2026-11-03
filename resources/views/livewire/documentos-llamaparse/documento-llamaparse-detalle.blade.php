@php
    $totalOk = data_get($json, 'resumen.total_ok');
    $totalInformado = data_get($json, 'documento.total_informado');
    $totalCalculado = data_get($json, 'documento.total_calculado');
    $formatear = fn ($valor) => $valor === null || $valor === ''
        ? '—'
        : '$ '.number_format((float) $valor, 2, ',', '.');
@endphp

<div class="container-fluid px-2 px-md-4 mt-3" @if ($documento->estado->enProceso()) wire:poll.4s="refrescar" @endif>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <a href="{{ route('documentos_llamaparse.index') }}" class="small text-decoration-none">
                <i class="fa-solid fa-arrow-left me-1"></i>Volver al listado
            </a>
            <h5 class="m-0 mt-1 font-weight-bold text-primary fs-6 text-truncate">
                <i class="fa-solid fa-cloud-arrow-up me-2"></i>{{ $documento->nombre_original }}
            </h5>
            <div class="small text-muted">
                {{ $documento->created_at?->format('d/m/Y H:i') }} ·
                {{ $documento->usuario?->name ?? 'Usuario desconocido' }} ·
                Tier {{ $documento->tier ?? '—' }}
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge {{ $documento->estado->badgeClass() }} fs-6">{{ $documento->estado->label() }}</span>
            <a href="{{ route('documentos_llamaparse.archivo', $documento) }}" target="_blank" rel="noopener"
                class="btn btn-outline-secondary btn-sm">
                <i class="fa-regular fa-file-pdf me-1"></i>Ver PDF
            </a>
            @if ($documento->xlsx_archivo)
                <a href="{{ route('documentos_llamaparse.xlsx', $documento) }}"
                    class="btn btn-outline-success btn-sm">
                    <i class="fa-regular fa-file-excel me-1"></i>Descargar XLSX
                </a>
            @endif
            @if (! $documento->estado->enProceso())
                <button type="button" class="btn btn-outline-primary btn-sm"
                    wire:click="reprocesar" wire:loading.attr="disabled" wire:target="reprocesar"
                    wire:confirm="¿Volver a enviar el PDF a LlamaParse? Se sobrescribira el resultado actual.">
                    <i class="fa-solid fa-rotate me-1"></i>Reprocesar
                </button>
            @endif
            <button type="button" class="btn btn-outline-danger btn-sm"
                wire:click="borrar" wire:loading.attr="disabled" wire:target="borrar"
                wire:confirm="¿Confirma eliminar este documento, el PDF y el XLSX generado?">
                <i class="fa-regular fa-trash-can me-1"></i>Eliminar
            </button>
        </div>
    </div>

    @include('utiles.alerts')

    @error('documento')
        <div class="alert alert-danger py-2 small">{{ $message }}</div>
    @enderror

    @if ($documento->estado->enProceso())
        <div class="alert alert-info d-flex align-items-center gap-2">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <div>
                {{ $documento->progreso_mensaje ?? 'Procesando en LlamaParse' }}
                <span class="text-muted">({{ $documento->progreso }}%)</span>
            </div>
        </div>
        <div class="progress mb-3" style="height: 8px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                style="width: {{ min(100, max(0, $documento->progreso)) }}%"
                aria-valuenow="{{ $documento->progreso }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    @endif

    @if ($documento->estado === \App\Enums\EstadoDocumentoLlamaparse::Error)
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Error al procesar</div>
            <div class="small">{{ $documento->error ?? 'Error desconocido.' }}</div>
            @if ($documento->job_id)
                <div class="small mt-1">
                    El PDF ya está en LlamaParse (trabajo {{ $documento->job_id }}). Podés reintentar la
                    consulta sin volver a subirlo ni consumir créditos.
                </div>
                <button type="button" class="btn btn-light btn-sm mt-2"
                    wire:click="reintentarConsulta" wire:loading.attr="disabled" wire:target="reintentarConsulta">
                    <i class="fa-solid fa-rotate-right me-1"></i>
                    <span wire:loading.remove wire:target="reintentarConsulta">Reintentar consulta</span>
                    <span wire:loading wire:target="reintentarConsulta">Consultando...</span>
                </button>
            @endif
        </div>
    @endif

    @if ($json !== null && ! $documento->estado->enProceso())
        <div
            class="alert alert-success d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-2">
            <div class="small">
                <i class="fa-solid fa-circle-check me-1"></i>
                <strong>JSON listo.</strong>
                Podés auditar este documento contra la base (partes, consumos, coberturas y niveles).
                <span class="text-muted">
                    <strong>Ver relaciones</strong> abre la tabla de relaciones y diferencias; si hay datos sin
                    relacionar, los muestra igual marcados como <em>No está en sistema</em> o
                    <em>Falta en el archivo</em>.
                </span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if ($auditoria)
                    <a href="{{ route('ordenes_pagos_audit.detalle', $auditoria) }}"
                        class="btn btn-outline-success btn-sm">
                        <i class="fa-solid fa-table-list me-1"></i>Ver relaciones
                    </a>
                    <button type="button" class="btn btn-success btn-sm"
                        wire:click="auditar" wire:loading.attr="disabled" wire:target="auditar"
                        wire:confirm="Ya existe una auditoría para este documento. ¿Recalcular y reemplazar los resultados?">
                        <i class="fa-solid fa-scale-balanced me-1"></i>
                        <span wire:loading.remove wire:target="auditar">Reauditar PDF</span>
                        <span wire:loading wire:target="auditar">Comparando...</span>
                    </button>
                @else
                    <button type="button" class="btn btn-outline-success btn-sm"
                        wire:click="verRelaciones" wire:loading.attr="disabled" wire:target="verRelaciones">
                        <i class="fa-solid fa-table-list me-1"></i>
                        <span wire:loading.remove wire:target="verRelaciones">Ver relaciones</span>
                        <span wire:loading wire:target="verRelaciones">Generando...</span>
                    </button>
                    <button type="button" class="btn btn-success btn-sm"
                        wire:click="auditar" wire:loading.attr="disabled" wire:target="auditar">
                        <i class="fa-solid fa-scale-balanced me-1"></i>
                        <span wire:loading.remove wire:target="auditar">Auditar PDF</span>
                        <span wire:loading wire:target="auditar">Comparando...</span>
                    </button>
                @endif
            </div>
        </div>
    @endif

    @if ($erroresAuditoria !== [])
        <div class="alert alert-warning py-2 small">
            <ul class="mb-0">
                @foreach ($erroresAuditoria as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('livewire.ordenes-pagos-audit.partials.inconsistencias-auditoria', [
        'items' => $inconsistenciasAuditoria,
        'nota' => 'No se guardó ningún resultado. Codificá los datos y volvé a auditar el documento, o usá «Ver relaciones» para abrir la tabla igual con las filas marcadas.',
    ])

    @if ($json !== null)
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-secondary">Tipo: {{ data_get($json, 'documento.tipo', '—') }}</span>
            <span class="badge bg-secondary">Atenciones: {{ data_get($json, 'resumen.atenciones_unicas', '—') }}</span>
            <span class="badge bg-secondary">Detalles: {{ data_get($json, 'resumen.detalles', '—') }}</span>
            <span class="badge bg-secondary">Total informado: {{ $formatear($totalInformado) }}</span>
            <span class="badge bg-secondary">Suma detalles: {{ $formatear($totalCalculado) }}</span>
            <span class="badge {{ $totalOk === true ? 'bg-success' : ($totalOk === false ? 'bg-danger' : 'bg-secondary') }}">
                {{ $totalOk === true ? 'Total coincide' : ($totalOk === false ? 'Total NO coincide' : 'Sin total para comparar') }}
            </span>
            @if ($documento->usage_credits !== null)
                <span class="badge bg-light text-dark border">
                    Creditos: {{ number_format((float) $documento->usage_credits, 2, ',', '.') }}
                </span>
            @endif
        </div>
    @endif

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 small fw-semibold">PDF original</div>
                <div class="card-body p-0">
                    <iframe src="{{ route('documentos_llamaparse.archivo', $documento) }}"
                        title="PDF original" style="width:100%;height:78vh;border:0;"></iframe>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 small fw-semibold d-flex justify-content-between align-items-center">
                    <span>JSON generado</span>
                    @if ($jsonPretty !== '')
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="copiarLlamaparseJson">
                            <i class="fa-regular fa-copy me-1"></i>Copiar
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if ($jsonPretty !== '')
                        <pre class="m-0 p-3 small" id="llamaparseJson"
                            style="max-height:78vh;overflow:auto;white-space:pre-wrap;word-break:break-word;">{{ $jsonPretty }}</pre>
                    @else
                        <div class="p-3 text-muted small">
                            El JSON estara disponible cuando LlamaParse termine de procesar.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($markdownPretty !== '')
        <div class="card shadow-sm mt-3">
            <div class="card-header py-2 small fw-semibold">Markdown devuelto por LlamaParse</div>
            <div class="collapse" id="llamaparseMarkdown">
                <pre class="m-0 p-3 small" style="max-height:60vh;overflow:auto;white-space:pre-wrap;word-break:break-word;">{{ $markdownPretty }}</pre>
            </div>
            <div class="card-body py-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                    data-bs-target="#llamaparseMarkdown" aria-expanded="false" aria-controls="llamaparseMarkdown">
                    Mostrar / ocultar
                </button>
            </div>
        </div>
    @endif

    @if ($itemsPretty !== '')
        <div class="card shadow-sm mt-3">
            <div class="card-header py-2 small fw-semibold">Items / tablas detectadas</div>
            <div class="collapse" id="llamaparseItems">
                <pre class="m-0 p-3 small" style="max-height:60vh;overflow:auto;white-space:pre-wrap;word-break:break-word;">{{ $itemsPretty }}</pre>
            </div>
            <div class="card-body py-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                    data-bs-target="#llamaparseItems" aria-expanded="false" aria-controls="llamaparseItems">
                    Mostrar / ocultar
                </button>
            </div>
        </div>
    @endif
</div>

<script>
    document.getElementById('copiarLlamaparseJson')?.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(document.getElementById('llamaparseJson').textContent);
            const boton = document.getElementById('copiarLlamaparseJson');
            boton.innerHTML = '<i class="fa-solid fa-check me-1"></i>Copiado';
            setTimeout(() => {
                boton.innerHTML = '<i class="fa-regular fa-copy me-1"></i>Copiar';
            }, 1500);
        } catch (e) {
            alert('No se pudo copiar el JSON.');
        }
    });
</script>
