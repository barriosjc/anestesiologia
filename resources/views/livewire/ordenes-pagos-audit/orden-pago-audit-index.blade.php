{{-- Listado de ordenes de pago auditadas.
     Tabla responsive: en celular se usa .ta-solo-movil (tarjetas) y desde 768px
     .ta-solo-escritorio (tabla), por el mismo criterio que el resto de los
     listados del sistema (ver public/css/custom.css). --}}
@php
    // Importe del archivo. NULL = el archivo no informa el dato.
    $importe = function ($valor): string {
        return $valor === null ? '—' : '$ '.number_format((float) $valor, 2, ',', '.');
    };

    // IB: 0 significa que la orden de pago no tiene retencion de Ingresos Brutos.
    $importeIb = function ($valor) use ($importe): string {
        return (float) $valor === 0.0 ? '—' : $importe($valor);
    };
@endphp
<div class="opa container-fluid px-2 px-md-4 mt-3">
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="m-0 font-weight-bold text-primary fs-6">
                    <i class="fa-solid fa-file-invoice-dollar me-2"></i>Ordenes de Pago - Auditoría
                </h5>
                <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                    <div class="input-group flex-grow-1">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="busqueda"
                            class="form-control form-control-sm"
                            placeholder="Buscar por Nro OP o profesional..."
                            aria-label="Buscar orden de pago"
                        />
                        <span class="input-group-text" wire:loading.delay wire:target="busqueda">
                            <span class="spinner-border spinner-border-sm" role="status" aria-label="Buscando"></span>
                        </span>
                    </div>
                    <a href="{{ route('documentos_llamaparse.index') }}"
                        class="btn btn-outline-secondary btn-sm text-nowrap">
                        <i class="fa-solid fa-file-pdf me-1"></i>PDFs procesados
                    </a>
                    <button
                        type="button"
                        wire:click="abrirModalAuditar"
                        wire:loading.attr="disabled"
                        wire:target="abrirModalAuditar"
                        class="btn btn-primary btn-sm text-nowrap"
                    >
                        <i class="fa-solid fa-scale-balanced me-1"></i>Auditar PDF
                        <span
                            wire:loading
                            wire:target="abrirModalAuditar"
                            class="spinner-border spinner-border-sm ms-1"
                            role="status"
                            aria-label="Abriendo el selector de documentos"
                        ></span>
                    </button>
                    <button
                        type="button"
                        wire:click="abrirModalSubir"
                        wire:loading.attr="disabled"
                        wire:target="abrirModalSubir"
                        class="btn btn-success btn-sm text-nowrap"
                    >
                        <i class="fa-solid fa-upload me-1"></i>Subir
                        <span
                            wire:loading
                            wire:target="abrirModalSubir"
                            class="spinner-border spinner-border-sm ms-1"
                            role="status"
                            aria-label="Abriendo el formulario de carga"
                        ></span>
                    </button>
                </div>
            </div>
            @if ($registros->total() > 0)
                <div class="small text-muted mt-2">
                    {{ number_format($registros->total(), 0, ',', '.') }}
                    {{ $registros->total() === 1 ? 'orden de pago' : 'ordenes de pago' }}
                </div>
            @endif
        </div>

        <div class="card-body p-0">
            @include('utiles.alerts')

            {{-- ─── Celulares: una tarjeta por registro ─────────────────────────── --}}
            <div class="ta-solo-movil ta-lista">
                @forelse ($registros as $item)
                    <article class="ta-card" wire:key="opa-tarjeta-{{ $item->id }}">
                        <div class="ta-card-head">
                            <div class="ta-card-paciente">OP {{ $item->nro_op_8 }}</div>
                            <span class="badge bg-light text-dark border">
                                {{ $item->fecha ? $item->fecha->format('d/m/Y') : '—' }}
                            </span>
                        </div>

                        <dl class="ta-card-body">
                            <div class="ta-dato">
                                <dt>Profesional</dt>
                                <dd>{{ $item->profesional?->nombre ?? '—' }}</dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Total</dt>
                                <dd>{{ $importe($item->op_total) }}</dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Retención</dt>
                                <dd>
                                    {{ $importe($item->caja_prof_salud) }}
                                    @if ($item->caja_no_informada)
                                        <i class="fa-solid fa-triangle-exclamation text-warning ms-1"
                                            title="La Caja de Prof. de la salud no viene informada en el PDF"></i>
                                    @endif
                                </dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Gast. Adm.</dt>
                                <dd>{{ $importe($item->gastos_admin) }}</dd>
                            </div>
                            <div class="ta-dato">
                                <dt>IIBB </dt>
                                <dd>{{ $importeIb($item->ib) }}</dd>
                            </div>
                            <div class="ta-dato">
                                <dt>A pagar</dt>
                                <dd class="fw-bold">{{ $importe($item->a_pagar) }}</dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Diferencias</dt>
                                <dd>
                                    @if ($item->auditado_at)
                                        <span class="badge {{ $item->cantidad_diferencias > 0 ? 'bg-warning text-dark' : 'bg-success' }}">
                                            {{ $item->cantidad_diferencias }}
                                        </span>
                                    @else
                                        <span class="text-muted">Sin auditar</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <div class="ta-card-foot">
                            @if ($item->auditado_at)
                                <a
                                    href="{{ route('ordenes_pagos_audit.detalle', $item) }}"
                                    class="btn btn-sm btn-outline-primary flex-fill ta-accion"
                                >
                                    <i class="fa-solid fa-scale-balanced me-1"></i>Ver diferencias
                                </a>
                            @endif
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger flex-fill ta-accion"
                                onclick="confirmDelete({{ $item->id }}, '¿Confirma eliminar la orden de pago {{ $item->nro_op }}?', () => @this.borrar({{ $item->id }}))"
                            >
                                <i class="fa-regular fa-trash-can me-1"></i>Eliminar
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="ta-vacio">
                        <i class="fa-solid fa-file-invoice-dollar fa-2x mb-2 text-secondary opacity-50"></i>
                        <div>No se encontraron ordenes de pago auditadas.</div>
                    </div>
                @endforelse
            </div>

            {{-- ─── Escritorio / tablet: tabla completa ─────────────────────────── --}}
            <div class="ta-solo-escritorio">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nro OP</th>
                                <th>Profesional</th>
                                <th>Fecha</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Retención (Caja Prof.)</th>
                                <th class="text-end">Gast. Adm.</th>
                                <th class="text-end">IB (Ingresos Brutos)</th>
                                <th class="text-end">A pagar</th>
                                <th class="text-center">Diferencias</th>
                                <th class="text-end pe-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($registros as $item)
                                <tr wire:key="opa-{{ $item->id }}">
                                    <td class="fw-semibold">{{ $item->nro_op_8 }}</td>
                                    <td>{{ $item->profesional?->nombre ?? '—' }}</td>
                                    <td>{{ $item->fecha ? $item->fecha->format('d/m/Y') : '—' }}</td>
                                    <td class="text-end text-nowrap">{{ $importe($item->op_total) }}</td>
                                    <td class="text-end text-nowrap">
                                        {{ $importe($item->caja_prof_salud) }}
                                        @if ($item->caja_no_informada)
                                            <i class="fa-solid fa-triangle-exclamation text-warning ms-1"
                                                title="La Caja de Prof. de la salud no viene informada en el PDF"></i>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">{{ $importe($item->gastos_admin) }}</td>
                                    <td class="text-end text-nowrap">{{ $importeIb($item->ib) }}</td>
                                    <td class="text-end text-nowrap fw-bold">{{ $importe($item->a_pagar) }}</td>
                                    <td class="text-center">
                                        @if ($item->auditado_at)
                                            <span class="badge {{ $item->cantidad_diferencias > 0 ? 'bg-warning text-dark' : 'bg-success' }}">
                                                {{ $item->cantidad_diferencias }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3 text-nowrap">
                                        @if ($item->auditado_at)
                                            <a
                                                href="{{ route('ordenes_pagos_audit.detalle', $item) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Ver diferencias"
                                            >
                                                <i class="fa-solid fa-scale-balanced"></i>
                                            </a>
                                        @endif
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Eliminar"
                                            onclick="confirmDelete({{ $item->id }}, '¿Confirma eliminar la orden de pago {{ $item->nro_op }}?', () => @this.borrar({{ $item->id }}))"
                                        >
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-file-invoice-dollar fa-2x mb-2 text-secondary opacity-50"></i>
                                        <div>No se encontraron ordenes de pago auditadas.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($registros->hasPages())
                <div class="card-footer py-2 ta-paginacion">
                    {{ $registros->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Modal de carga de archivo ─────────────────────────────────────────
         La subida NO usa wire:model: el navegador manda el archivo con un XHR
         normal a ordenes_pagos_audit.subir (ver public/js/upload-directo.js).
         Asi se ve el progreso real y los errores del servidor tal cual. --}}
    <div class="modal fade" id="opAuditUploadModal" tabindex="-1" aria-labelledby="opAuditUploadModalLabel"
        aria-hidden="true" wire:ignore.self
        data-upload-url="{{ route('ordenes_pagos_audit.subir') }}"
        data-upload-evento="orden-pago-audit-importada"
        data-upload-max-mb="{{ $this->maxArchivoMb }}"
        data-upload-extensiones="csv,txt,xls,xlsx,pdf">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="opAuditUploadModalLabel">
                        <i class="fa-solid fa-upload me-1"></i>Subir ordenes de pago
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small mb-1" for="opa_archivo">Archivo (CSV / XLSX / XLS / PDF)</label>
                        <input
                            type="file"
                            class="form-control form-control-sm"
                            id="opa_archivo"
                            accept=".csv,.txt,.xls,.xlsx,.pdf"
                            data-upload-input
                        />
                        <div class="form-text">
                            Máximo {{ $this->maxArchivoMb }} MB (límite de la aplicación y de PHP).
                        </div>
                        <div class="text-success small mt-1 d-none" data-upload-name></div>
                    </div>

                    <div class="alert alert-danger py-2 small d-none" data-upload-error></div>

                    <div class="d-none" data-upload-progress>
                        <div class="d-flex justify-content-between small mb-1">
                            <span data-upload-status>Subiendo archivo...</span>
                            <span data-upload-percent>0%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                style="width: 0%;" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                aria-valuenow="0" data-upload-bar></div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 small mb-0 mt-3">
                        <div class="mb-1">
                            <strong>CSV / XLSX / XLS:</strong> importa ordenes de pago. El archivo debe tener una fila
                            de encabezado con las columnas <strong>nro_op</strong>, <strong>profesional_id</strong>,
                            <strong>fecha</strong>, <strong>op_total</strong> y <strong>op_retenciones</strong>
                            (IB / Ingresos Brutos; tambien se acepta <strong>ib</strong> o
                            <strong>ingresos_brutos</strong>).
                            En <strong>profesional_id</strong> se puede indicar el id, el CUIT o el nombre del profesional.
                            Las ordenes con el mismo <strong>nro_op</strong> se actualizan.
                        </div>
                        <div>
                            <strong>PDF:</strong> se envia a LlamaParse y, al terminar, se abre el detalle con el
                            PDF, el XLSX generado y el JSON. Tambien podes consultar los PDFs procesados desde
                            el boton <strong>PDFs procesados</strong>.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" data-upload-close>
                        <i class="fa-solid fa-xmark me-1"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-sm btn-success" data-upload-submit disabled>
                        <i class="fa-solid fa-upload me-1"></i>Subir
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Modal de auditoria contra JSON procesado ─────────────────────────── --}}
    <div class="modal fade" id="opAuditAuditarModal" tabindex="-1" aria-labelledby="opAuditAuditarModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="opAuditAuditarModalLabel">
                        <i class="fa-solid fa-scale-balanced me-1"></i>Auditar PDF contra la base
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form wire:submit="auditar" novalidate>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="small mb-1" for="opa_buscar_documento">Buscar PDF procesado</label>
                            <input
                                wire:model.live.debounce.300ms="busquedaDocumento"
                                class="form-control form-control-sm"
                                id="opa_buscar_documento"
                                type="text"
                                placeholder="Nombre del archivo o UUID..."
                            />
                        </div>

                        <div class="mb-3">
                            <label class="small mb-1" for="opa_documento">Documento</label>
                            <select wire:model="documentoAuditarId" class="form-select form-select-sm" id="opa_documento">
                                <option value="">Seleccione un documento procesado...</option>
                                @foreach ($documentos as $documento)
                                    <option value="{{ $documento->id }}">
                                        {{ $documento->nombre_original }}
                                        @if ($documento->procesado_at)
                                            — {{ $documento->procesado_at->format('d/m/Y H:i') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('documentoAuditarId')
                                <span class="text-danger small d-block mt-1">{{ $message }}</span>
                            @enderror
                            @if ($documentos->isEmpty())
                                <span class="text-muted small d-block mt-1">
                                    No hay documentos procesados con JSON disponible.
                                </span>
                            @endif
                        </div>

                        <div class="alert alert-info py-2 small mb-0">
                            Se comparan las atenciones y los códigos del PDF contra
                            <strong>partes_cab</strong>, <strong>consumos_det</strong>,
                            <strong>coberturas.cod_uom</strong> y la tabla
                            <strong>niveles</strong>. Las diferencias se ordenan por porcentaje.
                        </div>

                        @if ($erroresAuditoria !== [])
                            <div class="alert alert-warning py-2 small mt-3 mb-0">
                                <ul class="mb-0">
                                    @foreach ($erroresAuditoria as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @include('livewire.ordenes-pagos-audit.partials.inconsistencias-auditoria', [
                            'items' => $inconsistenciasAuditoria,
                            'clase' => 'mt-3 mb-0',
                            'nota' => 'No se guardó ningún resultado. Codificá los datos y volvé a auditar el documento.',
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal"
                            wire:loading.attr="disabled" wire:target="auditar">
                            <i class="fa-solid fa-xmark me-1"></i>Cerrar
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary"
                            wire:loading.attr="disabled" wire:target="auditar">
                            <span wire:loading.remove wire:target="auditar">
                                <i class="fa-solid fa-scale-balanced me-1"></i>Auditar
                            </span>
                            <span wire:loading wire:target="auditar">
                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                    aria-hidden="true"></span>Comparando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @assets
        <script src="{{ asset('js/upload-directo.js') }}?v={{ filemtime(public_path('js/upload-directo.js')) }}"></script>
    @endassets

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-modal', (event) => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById(event.modal)).show();
            });
            Livewire.on('close-modal', (event) => {
                bootstrap.Modal.getInstance(document.getElementById(event.modal))?.hide();
            });
        });
    </script>
</div>
