{{-- Móvil primero: bajo 768px cada registro se muestra como una tarjeta
     (la tabla de 11 columnas no entra en un celular y obligaba a scrollear
     en horizontal). Desde 768px se muestra la tabla.
     La alternancia usa .ta-solo-movil / .ta-solo-escritorio y no los pares
     "d-none d-md-block" de Bootstrap: sbadmin/styles.css vuelve a declarar
     .d-none con !important después de Bootstrap y los anula. --}}
<div class="terceros-anestesia container-fluid px-2 px-md-4 mt-3">
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="m-0 font-weight-bold text-primary fs-6">
                    <i class="fa-solid fa-notes-medical me-2"></i>Reso / Tomo / Terceros con Anestesia
                </h5>
                <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                    <div class="input-group flex-grow-1">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="busqueda"
                            class="form-control form-control-sm"
                            placeholder="Buscar paciente..."
                            aria-label="Buscar paciente por nombre"
                        />
                        <span class="input-group-text" wire:loading.delay wire:target="busqueda">
                            <span class="spinner-border spinner-border-sm" role="status" aria-label="Buscando"></span>
                        </span>
                    </div>
                    {{-- El href queda como respaldo por si el JS no llegara a correr.
                         El .prevent es necesario: wire:click no cancela la
                         navegación nativa, así que sin él el navegador se iría
                         por su cuenta y el spinner no llegaría a verse. --}}
                    <a
                        href="{{ route('terceros_anestesia.create') }}"
                        wire:click.prevent="irACrear"
                        wire:loading.attr="disabled"
                        wire:loading.class="disabled"
                        wire:target="irACrear"
                        class="btn btn-primary btn-sm text-nowrap"
                    >
                        <i class="fa-solid fa-plus me-1"></i>Nuevo
                        <span
                            wire:loading
                            wire:target="irACrear"
                            class="spinner-border spinner-border-sm ms-1"
                            role="status"
                            aria-label="Abriendo el formulario"
                        ></span>
                    </a>
                </div>
            </div>
            @if ($registros->total() > 0)
                <div class="small text-muted mt-2">
                    {{ number_format($registros->total(), 0, ',', '.') }}
                    {{ $registros->total() === 1 ? 'registro' : 'registros' }}
                </div>
            @endif
        </div>

        <div class="card-body p-0">

            {{-- ─── Celulares: una tarjeta por registro ─────────────────────────── --}}
            <div class="ta-solo-movil ta-lista">
                @forelse ($registros as $item)
                    <article class="ta-card" wire:key="tarjeta-{{ $item->id }}">
                        <div class="ta-card-head">
                            <div class="ta-card-paciente">{{ $item->paciente }}</div>
                            <span class="badge {{ $item->estado->badgeClass() }}">{{ $item->estado->label() }}</span>
                        </div>

                        <div class="ta-card-meta">
                            <span>
                                <i class="fa-regular fa-calendar me-1"></i>{{ $item->fecha ? $item->fecha->format('d/m/Y') : '—' }}
                            </span>
                            <span>
                                <i class="fa-regular fa-clock me-1"></i>{{ $item->hora ? substr($item->hora, 0, 5) : '—' }}
                            </span>
                            @if ($item->urgencia)
                                <span class="badge bg-danger">Urgencia</span>
                            @endif
                            @if ($item->pasado_sistema)
                                <span class="badge bg-success">Medinexus</span>
                            @endif
                        </div>

                        <dl class="ta-card-body">
                            <div class="ta-dato">
                                <dt>Procedimiento</dt>
                                <dd>
                                    @if ($item->nomenclador)
                                        <span class="badge bg-light text-dark border">{{ $item->nomenclador->codigo }}</span>
                                        <span class="small">{{ $item->nomenclador->descripcion }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Anestesiólogo</dt>
                                <dd>{{ $item->profesional?->nombre ?? '—' }}</dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Cobertura</dt>
                                <dd>
                                    @if ($item->cobertura)
                                        <span class="badge bg-light text-dark border">{{ $item->cobertura->sigla ?: $item->cobertura->nombre }}</span>
                                        <span class="small text-muted">{{ $item->cobertura->nombre }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($item->observaciones)
                                <div class="ta-dato">
                                    <dt>Observaciones</dt>
                                    <dd class="ta-observaciones">{{ $item->observaciones }}</dd>
                                </div>
                            @endif
                        </dl>

                        <div class="ta-card-foot">
                            <a
                                href="{{ route('terceros_anestesia.edit', $item->id) }}"
                                wire:click.prevent="irAEditar({{ $item->id }})"
                                wire:loading.attr="disabled"
                                wire:loading.class="disabled"
                                wire:target="irAEditar({{ $item->id }})"
                                class="btn btn-sm btn-outline-primary flex-fill ta-accion"
                            >
                                <i class="fa-solid fa-pen-to-square me-1"></i>Editar
                                <span
                                    wire:loading
                                    wire:target="irAEditar({{ $item->id }})"
                                    class="spinner-border spinner-border-sm ms-1"
                                    role="status"
                                    aria-label="Abriendo el formulario"
                                ></span>
                            </a>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger flex-fill ta-accion"
                                onclick="confirmDelete({{ $item->id }}, '¿Confirma eliminar este registro de {{ addslashes($item->paciente) }}?', () => @this.borrar({{ $item->id }}))"
                            >
                                <i class="fa-regular fa-trash-can me-1"></i>Eliminar
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="ta-vacio">
                        <i class="fa-solid fa-notes-medical fa-2x mb-2 text-secondary opacity-50"></i>
                        <div>No se encontraron registros de Terceros con Anestesia.</div>
                    </div>
                @endforelse
            </div>

            {{-- ─── Escritorio / tablet: tabla completa ───────────────────────────── --}}
            <div class="ta-solo-escritorio">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Procedimiento</th>
                                <th>Estado</th>
                                <th>Anestesiólogo</th>
                                <th>Cobertura</th>
                                <th>Urgencia</th>
                                <th>Observaciones</th>
                                <th>Medinexus</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($registros as $item)
                                <tr wire:key="registro-{{ $item->id }}">
                                    <td class="text-nowrap">{{ $item->fecha ? $item->fecha->format('d/m/Y') : '—' }}</td>
                                    <td class="text-nowrap">{{ $item->hora ? substr($item->hora, 0, 5) : '—' }}</td>
                                    <td class="fw-semibold text-dark">{{ $item->paciente }}</td>
                                    <td>
                                        @if ($item->nomenclador)
                                            <span class="badge bg-light text-dark border">
                                                {{ $item->nomenclador->codigo }}
                                            </span>
                                            <span class="small">{{ $item->nomenclador->descripcion }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->estado->badgeClass() }}">
                                            {{ $item->estado->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $item->profesional?->nombre ?? '—' }}</td>
                                    <td class="text-nowrap">
                                        @if ($item->cobertura)
                                            <span class="badge bg-light text-dark border">{{ $item->cobertura->sigla ?: $item->cobertura->nombre }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->urgencia)
                                            <span class="badge bg-danger">Sí</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->observaciones)
                                            <span title="{{ $item->observaciones }}" data-bs-toggle="tooltip">
                                                <i class="fa-regular fa-comment-dots text-secondary"></i>
                                                <span class="small text-muted d-inline-block text-truncate ta-obs-desktop">
                                                    {{ $item->observaciones }}
                                                </span>
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->pasado_sistema)
                                            <span class="badge bg-success">Sí</span>
                                        @else
                                            <span class="badge bg-light text-muted border">No</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3 text-nowrap">
                                        <a
                                            href="{{ route('terceros_anestesia.edit', $item->id) }}"
                                            wire:click.prevent="irAEditar({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="disabled"
                                            wire:target="irAEditar({{ $item->id }})"
                                            class="btn btn-sm btn-outline-primary me-1"
                                            title="Editar"
                                        >
                                            <i
                                                wire:loading.remove
                                                wire:target="irAEditar({{ $item->id }})"
                                                class="fa-solid fa-pen-to-square"
                                            ></i>
                                            <span
                                                wire:loading
                                                wire:target="irAEditar({{ $item->id }})"
                                                class="spinner-border spinner-border-sm"
                                                role="status"
                                                aria-label="Abriendo el formulario"
                                            ></span>
                                        </a>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Eliminar"
                                            onclick="confirmDelete({{ $item->id }}, '¿Confirma eliminar este registro de {{ addslashes($item->paciente) }}?', () => @this.borrar({{ $item->id }}))"
                                        >
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-notes-medical fa-2x mb-2 text-secondary opacity-50"></i>
                                        <div>No se encontraron registros de Terceros con Anestesia.</div>
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
</div>