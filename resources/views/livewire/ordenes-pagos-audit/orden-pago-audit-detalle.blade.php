@php
    $severidades = [
        'alta' => 'Alta',
        'media' => 'Media',
        'baja' => 'Baja',
        'sin-diferencia' => 'Sin diferencia',
    ];
    $estados = [
        'ok' => 'Coincide',
        'diferencia' => 'Diferencia',
        'sin_parte' => 'No está en sistema (atención)',
        'sin_nivel' => 'No está en sistema (código)',
        'sin_consumo' => 'No está en sistema (práctica)',
        'sin_archivo' => 'Falta en el archivo',
        'sin_correspondencia' => 'Sin contraparte en sistema',
    ];
@endphp

<div class="opa opa-audit container-fluid px-2 px-md-4 mt-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <a href="{{ route('ordenes_pagos_audit.cab') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fa-solid fa-arrow-left me-1"></i>Volver
            </a>
            <h5 class="m-0 font-weight-bold text-primary fs-5">
                <i class="fa-solid fa-scale-balanced me-2"></i>
                Auditoría OP {{ $orden->nro_op }}
                @if ($orden->nro_liquidacion)
                    <span class="text-muted fs-6">/ Liq. {{ $orden->nro_liquidacion }}</span>
                @endif
            </h5>
            <div class="small text-muted mt-1">
                {{ $orden->profesional?->nombre ?? 'Profesional no identificado' }}
                @if ($orden->fecha)
                    · {{ $orden->fecha->format('d/m/Y') }}
                @endif
                @if ($orden->documento)
                    · PDF: {{ $orden->documento->nombre_original }}
                @endif
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if ($orden->auditado_at)
                <span class="small text-muted">
                    Auditado {{ $orden->auditado_at->format('d/m/Y H:i') }}
                </span>
            @endif
            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="reauditar"
                wire:loading.attr="disabled" wire:target="reauditar">
                <i class="fa-solid fa-rotate me-1"></i>Recalcular
                <span wire:loading wire:target="reauditar" class="spinner-border spinner-border-sm ms-1"
                    role="status" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    @include('utiles.alerts')

    @if ($mensaje)
        <div class="alert alert-{{ $mensajeTipo }} py-2 mb-3">{{ $mensaje }}</div>
    @endif

    @include('livewire.ordenes-pagos-audit.partials.inconsistencias-auditoria', [
        'items' => $inconsistenciasAuditoria,
        'titulo' => 'No se pudo recalcular: estos datos del archivo no tienen código para relacionar. La auditoría anterior quedó sin cambios.',
    ])

    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card shadow-sm h-100">
                <div class="card-body py-2 px-3">
                    <div class="small text-muted">Total archivo</div>
                    <div class="fw-semibold">$ {{ number_format((float) $orden->op_total, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card shadow-sm h-100">
                <div class="card-body py-2 px-3">
                    <div class="small text-muted">Total sistema</div>
                    <div class="fw-semibold">$ {{ number_format((float) $orden->total_sistema, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card shadow-sm h-100">
                <div class="card-body py-2 px-3">
                    <div class="small text-muted">Diferencia</div>
                    <div class="fw-semibold {{ (float) $orden->total_diferencia == 0.0 ? 'text-success' : 'text-danger' }}">
                        $ {{ number_format((float) $orden->total_diferencia, 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card shadow-sm h-100">
                <div class="card-body py-2 px-3">
                    <div class="small text-muted">Filas con diferencia</div>
                    <div class="fw-semibold">
                        <span class="badge {{ $orden->cantidad_diferencias > 0 ? 'bg-warning text-dark' : 'bg-success' }}">
                            {{ $orden->cantidad_diferencias }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body py-2 px-3 d-flex flex-wrap gap-2 align-items-center">
                    @foreach ($severidades as $clave => $label)
                        <span class="badge {{ match ($clave) {
                            'alta' => 'bg-danger',
                            'media' => 'bg-warning text-dark',
                            'baja' => 'bg-info text-dark',
                            default => 'bg-success',
                        } }}">
                            {{ $label }}: {{ $resumenSeveridad->get($clave, 0) }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="small mb-1" for="opa_audit_busqueda">Buscar</label>
                    <input
                        type="text"
                        id="opa_audit_busqueda"
                        wire:model.live.debounce.300ms="busqueda"
                        class="form-control form-control-sm"
                        placeholder="Atención, código, nivel o descripción..."
                    />
                </div>
                <div class="col-6 col-md-2">
                    <label class="small mb-1" for="opa_audit_estado">Estado</label>
                    <select id="opa_audit_estado" wire:model.live="filtroEstado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach ($estados as $clave => $label)
                            <option value="{{ $clave }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="small mb-1" for="opa_audit_severidad">Gravedad</label>
                    <select id="opa_audit_severidad" wire:model.live="filtroSeveridad" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach ($severidades as $clave => $label)
                            <option value="{{ $clave }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-check mt-md-4">
                        <input class="form-check-input" type="checkbox" id="opa_audit_solo_dif"
                            wire:model.live="soloDiferencias" />
                        <label class="form-check-label small" for="opa_audit_solo_dif">
                            Solo diferencias
                        </label>
                    </div>
                </div>
                <div class="col-12 col-md-2 text-md-end">
                    <div class="d-grid gap-1">
                        <button type="button" class="btn btn-sm btn-success" wire:click="pasarAPagado"
                            wire:loading.attr="disabled" wire:target="pasarAPagado"
                            title="Pasa a pagado los consumos de las filas seleccionadas">
                            <i class="fa-solid fa-money-check-dollar me-1"></i>Cambiar a pagado
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="limpiar">
                            <i class="fa-solid fa-eraser me-1"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header py-2 small text-muted d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>
                @if (count($seleccionados) > 0)
                    <span class="badge bg-primary me-1">{{ count($seleccionados) }} seleccionada(s)</span>
                @endif
                {{ number_format($detalles->total(), 0, ',', '.') }}
                {{ $detalles->total() === 1 ? 'fila' : 'filas' }}
                @if ($resumenEstados->isNotEmpty())
                    ·
                    @foreach ($estados as $clave => $label)
                        @if ($resumenEstados->get($clave, 0) > 0)
                            {{ $label }}: {{ $resumenEstados->get($clave) }}@if (! $loop->last && $resumenEstados->get($clave, 0) > 0) · @endif
                        @endif
                    @endforeach
                @endif
            </span>

            @if ($etiquetaOrden !== '')
                <span class="d-inline-flex align-items-center gap-2">
                    <span>
                        Ordenado por <strong>{{ $etiquetaOrden }}</strong>
                        <i class="fa-solid {{ $iconosOrden[$ordenPor] }}"></i>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2"
                        wire:click="ordenPorDefecto">
                        <i class="fa-solid fa-rotate-left me-1"></i>Orden por gravedad
                    </button>
                </span>
            @endif
        </div>

        <div class="card-body p-0">
            {{-- ─── Celulares: una tarjeta por fila ─────────────────────────── --}}
            <div class="ta-solo-movil ta-lista">
                @forelse ($detalles as $detalle)
                    <article class="ta-card opa-fila-{{ $detalle->severidad }}" wire:key="opa-det-card-{{ $detalle->id }}">
                        <div class="ta-card-head">
                            <div class="ta-card-paciente">
                                <span class="opa-marca-{{ $colorGrupo[$detalle->id] ?? 'azul' }}"
                                    aria-hidden="true"></span>
                                @can('adm_consumos')
                                    @if ($detalle->parte_cab_id)
                                        <a href="{{ route('consumo.rendiciones.filtrar', ['nro_parte' => $detalle->parte_cab_id]) }}"
                                            target="_blank" rel="noopener" class="text-decoration-underline"
                                            title="Ver las rendiciones del parte {{ $detalle->parte_cab_id }} (atención {{ $detalle->atencion }})">
                                            Atención {{ $detalle->atencion ?: '—' }}
                                        </a>
                                    @else
                                        Atención {{ $detalle->atencion ?: '—' }}
                                    @endif
                                @else
                                    Atención {{ $detalle->atencion ?: '—' }}
                                @endcan
                                <span class="badge {{ $detalle->severidad_badge }} ms-1">
                                    {{ $detalle->severidad_label }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $detalle->estado_badge }}">{{ $detalle->estado_label }}</span>
                                <input class="form-check-input m-0" type="checkbox" aria-label="Seleccionar fila"
                                    wire:model.live="seleccionados" value="{{ $detalle->id }}">
                            </div>
                        </div>

                        <dl class="ta-card-body">
                            <div class="ta-dato">
                                <dt>Código / nivel</dt>
                                <dd>
                                    {{ $detalle->codigo_archivo ?: $detalle->codigo_sistema ?: '—' }}
                                    @if ($detalle->nivel_sistema)
                                        / {{ $detalle->nivel_sistema }}
                                    @endif
                                    @if ($detalle->codigo_sistema && $detalle->codigo_sistema !== $detalle->codigo_archivo)
                                        <br>
                                        <span class="text-muted">Sist: {{ $detalle->codigo_sistema }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="ta-dato">
                                <dt>% nivel</dt>
                                <dd>
                                    Archivo: {{ $detalle->porcentaje_archivo !== null ? number_format((float) $detalle->porcentaje_archivo, 2, ',', '.') . '%' : '—' }}
                                    <br>
                                    Sistema: {{ $detalle->porcentaje_sistema !== null ? number_format((float) $detalle->porcentaje_sistema, 2, ',', '.') . '%' : '—' }}
                                </dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Descripción</dt>
                                <dd>
                                    @if ($detalle->dif_descripcion)
                                        @include('livewire.ordenes-pagos-audit.partials.dif-punto', [
                                            'titulo' => 'Descripción distinta — Archivo: '.($detalle->descripcion_archivo ?: 'sin dato').' / Sistema: '.($detalle->descripcion_sistema ?: 'sin dato'),
                                        ])
                                    @endif
                                    {{ $detalle->descripcion_archivo ?: '—' }}
                                </dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Fecha</dt>
                                <dd>
                                    @if ($detalle->dif_fecha)
                                        @include('livewire.ordenes-pagos-audit.partials.dif-punto', [
                                            'titulo' => 'Fecha distinta — Archivo: '.$detalle->fecha_archivo?->format('d/m/Y').' / Sistema: '.$detalle->fecha_sistema?->format('d/m/Y'),
                                        ])
                                    @endif
                                    Archivo: {{ $detalle->fecha_archivo?->format('d/m/Y') ?? '—' }}
                                    <br>
                                    Sistema: {{ $detalle->fecha_sistema?->format('d/m/Y') ?? '—' }}
                                </dd>
                            </div>
                            <div class="ta-dato">
                                <dt>O.S.</dt>
                                <dd>
                                    @if ($detalle->dif_obra_social)
                                        @include('livewire.ordenes-pagos-audit.partials.dif-punto', [
                                            'titulo' => 'Obra social distinta — Archivo: '.($detalle->obra_social_archivo ?: 'sin dato').' / Sistema: '.($detalle->cod_uom_sistema ?: 'sin cod_uom'),
                                        ])
                                    @endif
                                    {{ $detalle->cod_uom_archivo ?: '—' }}
                                    / {{ $detalle->cod_uom_sistema ?: '—' }}
                                </dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Período</dt>
                                <dd>{{ $detalle->periodo_archivo ?: '—' }}</dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Valor archivo</dt>
                                <dd>
                                    @if ($detalle->estado === 'diferencia' && $detalle->dif_valor)
                                        @include('livewire.ordenes-pagos-audit.partials.dif-punto', [
                                            'titulo' => 'Valor distinto — Archivo: $ '.number_format((float) $detalle->valor_archivo, 2, ',', '.').' / Sistema: $ '.number_format((float) $detalle->valor_sistema, 2, ',', '.'),
                                        ])
                                    @endif
                                    $ {{ number_format((float) $detalle->valor_archivo, 2, ',', '.') }}
                                </dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Valor sistema</dt>
                                <dd>$ {{ number_format((float) $detalle->valor_sistema, 2, ',', '.') }}</dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Diferencia</dt>
                                <dd>
                                    $ {{ number_format((float) $detalle->diferencia, 2, ',', '.') }}
                                    <span class="text-muted">({{ number_format((float) $detalle->porcentaje_diferencia, 2, ',', '.') }}%)</span>
                                </dd>
                            </div>
                        </dl>

                        <div class="ta-card-foot d-flex flex-wrap align-items-center gap-1">
                            <span class="small text-muted me-1">Consumo:</span>
                            @include('livewire.ordenes-pagos-audit.partials.estado-consumo')
                            <button type="button" class="btn btn-sm btn-outline-success ta-accion"
                                onclick="confirmAction('Pasar a pagado', '¿Confirma pasar a pagado el consumo de esta fila?', () => @this.pasarAPagadoFila({{ $detalle->id }}))">
                                <i class="fa-solid fa-money-check-dollar me-1"></i>Pasar pagado
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info ta-accion"
                                wire:click="abrirRefacturar({{ $detalle->id }})">
                                <i class="fa-solid fa-rotate me-1"></i>Pasar a refacturar
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="ta-vacio">
                        <i class="fa-solid fa-scale-balanced fa-2x mb-2 text-secondary opacity-50"></i>
                        <div>No hay filas para los filtros seleccionados.</div>
                    </div>
                @endforelse
            </div>

            {{-- ─── Escritorio / tablet: tabla completa ─────────────────────── --}}
            <div class="ta-solo-escritorio">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 opa-audit-tabla">
                        <thead class="table-light">
                            <tr>
                                @php
                                    $th = 'btn btn-link btn-sm p-0 text-decoration-none text-body d-inline-flex align-items-center gap-1 text-nowrap';
                                @endphp
                                <th class="text-center" style="width: 38px">
                                    <input class="form-check-input" type="checkbox"
                                        wire:click="toggleAll($event.target.checked)"
                                        @checked($todosSeleccionados)
                                        title="Seleccionar todas las filas de la pantalla"
                                        aria-label="Seleccionar todas las filas de la pantalla">
                                </th>
                                <th>
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('atencion')">
                                        Atención <i class="fa-solid {{ $iconosOrden['atencion'] }} small"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('fecha')">
                                        Fecha <i class="fa-solid {{ $iconosOrden['fecha'] }} small"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('obra_social')">
                                        Obra social <i class="fa-solid {{ $iconosOrden['obra_social'] }} small"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('periodo')">
                                        Período <i class="fa-solid {{ $iconosOrden['periodo'] }} small"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('codigo')">
                                        Código / nivel <i class="fa-solid {{ $iconosOrden['codigo'] }} small"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('descripcion')">
                                        Descripción <i class="fa-solid {{ $iconosOrden['descripcion'] }} small"></i>
                                    </button>
                                </th>
                                <th class="text-end">
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('porcentaje_nivel')">
                                        % nivel <i class="fa-solid {{ $iconosOrden['porcentaje_nivel'] }} small"></i>
                                    </button>
                                </th>
                                <th class="text-end">
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('valor_archivo')">
                                        Valor archivo <i class="fa-solid {{ $iconosOrden['valor_archivo'] }} small"></i>
                                    </button>
                                </th>
                                <th class="text-end">
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('valor_sistema')">
                                        Valor sistema <i class="fa-solid {{ $iconosOrden['valor_sistema'] }} small"></i>
                                    </button>
                                </th>
                                <th class="text-end">
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('diferencia')">
                                        Diferencia <i class="fa-solid {{ $iconosOrden['diferencia'] }} small"></i>
                                    </button>
                                </th>
                                <th class="text-end">
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('porcentaje_dif')">
                                        % dif. <i class="fa-solid {{ $iconosOrden['porcentaje_dif'] }} small"></i>
                                    </button>
                                </th>
                                <th class="text-center">
                                    <button type="button" class="{{ $th }}"
                                        wire:click="ordenarPor('estado')">
                                        Estado <i class="fa-solid {{ $iconosOrden['estado'] }} small"></i>
                                    </button>
                                </th>
                                <th class="text-center" style="width: 48px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($detalles as $detalle)
                                <tr class="opa-fila-{{ $detalle->severidad }}" wire:key="opa-det-{{ $detalle->id }}">
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" aria-label="Seleccionar fila"
                                            wire:model.live="seleccionados" value="{{ $detalle->id }}">
                                    </td>
                                    <td class="fw-semibold text-nowrap opa-atencion opa-grupo-{{ $colorGrupo[$detalle->id] ?? 'azul' }}">
                                        @can('adm_consumos')
                                            @if ($detalle->parte_cab_id)
                                                <a href="{{ route('consumo.rendiciones.filtrar', ['nro_parte' => $detalle->parte_cab_id]) }}"
                                                    target="_blank" rel="noopener" class="text-decoration-underline"
                                                    title="Ver las rendiciones del parte {{ $detalle->parte_cab_id }} (atención {{ $detalle->atencion }})">
                                                    {{ $detalle->atencion ?: '—' }}
                                                </a>
                                            @else
                                                {{ $detalle->atencion ?: '—' }}
                                            @endif
                                        @else
                                            {{ $detalle->atencion ?: '—' }}
                                        @endcan
                                    </td>
                                    <td class="text-nowrap small">
                                        @if ($detalle->dif_fecha)
                                            @include('livewire.ordenes-pagos-audit.partials.dif-punto', [
                                                'titulo' => 'Fecha distinta — Archivo: '.$detalle->fecha_archivo?->format('d/m/Y').' / Sistema: '.$detalle->fecha_sistema?->format('d/m/Y'),
                                            ])
                                        @endif
                                        {{ $detalle->fecha_archivo?->format('d/m/Y') ?? '—' }}
                                        <br>
                                        <span class="text-muted">Sist: {{ $detalle->fecha_sistema?->format('d/m/Y') ?? '—' }}</span>
                                    </td>
                                    <td class="text-nowrap small">
                                        @if ($detalle->dif_obra_social)
                                            @include('livewire.ordenes-pagos-audit.partials.dif-punto', [
                                                'titulo' => 'Obra social distinta — Archivo: '.($detalle->obra_social_archivo ?: 'sin dato').' / Sistema: '.($detalle->cod_uom_sistema ?: 'sin cod_uom'),
                                            ])
                                        @endif
                                        {{ $detalle->cod_uom_archivo ?: '—' }}
                                        <br>
                                        <span class="text-muted">Sist: {{ $detalle->cod_uom_sistema ?: '—' }}</span>
                                    </td>
                                    <td class="small text-nowrap">{{ $detalle->periodo_archivo ?: '—' }}</td>
                                    <td class="text-nowrap">
                                        <span class="fw-semibold">{{ $detalle->codigo_archivo ?: $detalle->codigo_sistema ?: '—' }}</span>
                                        @if ($detalle->nivel_sistema)
                                            <span class="text-muted">/ {{ $detalle->nivel_sistema }}</span>
                                        @endif
                                        @if ($detalle->codigo_sistema && $detalle->codigo_sistema !== $detalle->codigo_archivo)
                                            <br>
                                            <span class="text-muted">Sist: {{ $detalle->codigo_sistema }}</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @php
                                            $descArchivo = $detalle->descripcion_archivo ?: '—';
                                            $descCompleta = $descArchivo;
                                            if ($detalle->descripcion_sistema && $detalle->descripcion_sistema !== $detalle->descripcion_archivo) {
                                                $descCompleta .= ' · Sistema: '.$detalle->descripcion_sistema;
                                            }
                                        @endphp
                                        <div class="d-flex align-items-center gap-1">
                                            @if ($detalle->dif_descripcion)
                                                @include('livewire.ordenes-pagos-audit.partials.dif-punto', [
                                                    'titulo' => 'Descripción distinta — Archivo: '.($detalle->descripcion_archivo ?: 'sin dato').' / Sistema: '.($detalle->descripcion_sistema ?: 'sin dato'),
                                                ])
                                            @endif
                                            <div class="text-truncate" style="max-width: 22rem"
                                                data-bs-placement="top"
                                                data-bs-title="{{ $descCompleta }}"
                                                x-data x-init="new bootstrap.Tooltip($el)">
                                                {{ $descArchivo }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end text-nowrap small">
                                        {{ $detalle->porcentaje_archivo !== null ? number_format((float) $detalle->porcentaje_archivo, 2, ',', '.') . '%' : '—' }}
                                        <br>
                                        <span class="text-muted">Sist: {{ $detalle->porcentaje_sistema !== null ? number_format((float) $detalle->porcentaje_sistema, 2, ',', '.') . '%' : '—' }}</span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @if ($detalle->estado === 'diferencia' && $detalle->dif_valor)
                                            @include('livewire.ordenes-pagos-audit.partials.dif-punto', [
                                                'titulo' => 'Valor distinto — Archivo: $ '.number_format((float) $detalle->valor_archivo, 2, ',', '.').' / Sistema: $ '.number_format((float) $detalle->valor_sistema, 2, ',', '.'),
                                            ])
                                        @endif
                                        $ {{ number_format((float) $detalle->valor_archivo, 2, ',', '.') }}
                                    </td>
                                    <td class="text-end text-nowrap">$ {{ number_format((float) $detalle->valor_sistema, 2, ',', '.') }}</td>
                                    <td class="text-end text-nowrap fw-semibold">
                                        {{ (float) $detalle->diferencia > 0 ? '+' : '' }}$ {{ number_format((float) $detalle->diferencia, 2, ',', '.') }}
                                    </td>
                                    <td class="text-end text-nowrap">{{ number_format((float) $detalle->porcentaje_diferencia, 2, ',', '.') }}%</td>
                                    <td class="text-center">
                                        <span class="badge {{ $detalle->estado_badge }}">{{ $detalle->estado_label }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            @include('livewire.ordenes-pagos-audit.partials.estado-consumo')
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-body p-0 px-1" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false"
                                                    title="Acciones de la fila">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    <li>
                                                        <button type="button" class="dropdown-item small"
                                                            onclick="confirmAction('Pasar a pagado', '¿Confirma pasar a pagado el consumo de esta fila?', () => @this.pasarAPagadoFila({{ $detalle->id }}))">
                                                            <i class="fa-solid fa-money-check-dollar me-2 text-success"></i>Pasar pagado
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item small"
                                                            wire:click="abrirRefacturar({{ $detalle->id }})">
                                                            <i class="fa-solid fa-rotate me-2 text-info"></i>Pasar a refacturar
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-scale-balanced fa-2x mb-2 text-secondary opacity-50"></i>
                                        <div>No hay filas para los filtros seleccionados.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($detalles->hasPages())
                <div class="card-footer py-2 ta-paginacion">
                    {{ $detalles->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="small text-muted mt-2">
        Colores: <span class="badge bg-info text-dark">Baja</span> hasta 5% ·
        <span class="badge bg-warning text-dark">Media</span> hasta 20% ·
        <span class="badge bg-danger">Alta</span> más de 20% o dato faltante.
    </div>

    {{-- modal: cambiar estado del parte --}}
    @include('cargas.cab.partials.cambio_estado', ['estados' => $estadosParte])

    {{-- modal: pasar a refacturar --}}
    <div class="modal fade" id="opaRefacturarModal" tabindex="-1" aria-labelledby="opaRefacturarModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="opaRefacturarModalLabel">
                        <i class="fa-solid fa-rotate me-2"></i>Pasar a refacturar
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form wire:submit="cambiarEstadoRefacturar">
                    <div class="modal-body">
                        @if ($mensaje && $mensajeTipo === 'danger')
                            <div class="alert alert-danger py-2 small mb-3">{{ $mensaje }}</div>
                        @endif

                        @if ($filaRefacDetalle)
                            <dl class="row small mb-3">
                                <dt class="col-5 text-muted fw-normal">Atención</dt>
                                <dd class="col-7 fw-semibold">{{ $filaRefacDetalle->atencion ?: '—' }}</dd>
                                <dt class="col-5 text-muted fw-normal">Código / nivel</dt>
                                <dd class="col-7">
                                    {{ $filaRefacDetalle->codigo_archivo ?: $filaRefacDetalle->codigo_sistema ?: '—' }}
                                    @if ($filaRefacDetalle->nivel_sistema)
                                        / {{ $filaRefacDetalle->nivel_sistema }}
                                    @endif
                                </dd>
                                <dt class="col-5 text-muted fw-normal">Descripción</dt>
                                <dd class="col-7">{{ $filaRefacDetalle->descripcion_archivo ?: '—' }}</dd>
                                <dt class="col-5 text-muted fw-normal">Valor sistema</dt>
                                <dd class="col-7">$ {{ number_format((float) $filaRefacDetalle->valor_sistema, 2, ',', '.') }}</dd>
                            </dl>
                        @endif

                        <label class="form-label small mb-1" for="opa_periodo_refac">Período a refacturar</label>
                        <select class="form-select form-select-sm" id="opa_periodo_refac" wire:model="periodoRefac">
                            @foreach ($periodos as $periodo)
                                <option value="{{ $periodo }}">{{ $periodo }}</option>
                            @endforeach
                        </select>
                        @error('periodoRefac') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-sm btn-info" wire:loading.attr="disabled"
                            wire:target="cambiarEstadoRefacturar">
                            <i class="fa-solid fa-rotate me-1"></i>Cambiar estado
                            <span wire:loading wire:target="cambiarEstadoRefacturar"
                                class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-modal', (event) => {
                const modal = document.getElementById(event.modal);
                if (modal) {
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
            Livewire.on('close-modal', (event) => {
                bootstrap.Modal.getInstance(document.getElementById(event.modal))?.hide();
            });
        });
    </script>
</div>
