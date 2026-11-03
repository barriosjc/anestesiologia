<div class="container-fluid px-2 px-md-4 mt-3" @if ($hayEnProceso) wire:poll.5s="consultarTodos" @endif>
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="m-0 font-weight-bold text-primary fs-6">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i>Documentos LlamaParse
                </h5>
                <div class="small text-muted">
                    {{ number_format($documentos->total(), 0, ',', '.') }}
                    {{ $documentos->total() === 1 ? 'documento' : 'documentos' }}
                </div>
            </div>
            <div class="small text-muted mt-1">
                PDF procesado por la API de LlamaParse. El XLSX de tablas se convierte al mismo JSON del modulo XLSX.
            </div>
        </div>

        <div class="card-body">
            @if (! $disponible)
                <div class="alert alert-warning py-2 small">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    LlamaParse no esta configurado. Agregá <code>LLAMAPARSE_API_KEY</code> en el <code>.env</code>.
                </div>
            @endif

            <form wire:submit="subir" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label small mb-1" for="llamaparse_archivo">Archivo PDF</label>
                    <input type="file" id="llamaparse_archivo" class="form-control form-control-sm"
                        wire:model="archivo" accept=".pdf" @disabled(! $disponible)
                        wire:loading.attr="disabled" wire:target="subir" />
                    @error('archivo')
                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                    @enderror
                    <div class="form-text">
                        PDF maximo {{ (int) config('llamaparse.max_file_mb', 100) }} MB. Se guarda en storage privado.
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1" for="llamaparse_estado">Estado</label>
                    <select id="llamaparse_estado" class="form-select form-select-sm" wire:model.live="estado">
                        <option value="">Todos</option>
                        @foreach ($estados as $estadoOpcion)
                            <option value="{{ $estadoOpcion->value }}">{{ $estadoOpcion->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-success btn-sm w-100"
                        @disabled(! $disponible) wire:loading.attr="disabled" wire:target="archivo,subir">
                        <span wire:loading.remove wire:target="subir">
                            <i class="fa-solid fa-upload me-1"></i>Subir a LlamaParse
                        </span>
                        <span wire:loading wire:target="subir">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Subiendo...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body p-0 border-top">
            <div class="ta-solo-movil ta-lista">
                @forelse ($documentos as $documento)
                    <article class="ta-card" wire:key="llamaparse-tarjeta-{{ $documento->id }}">
                        <div class="ta-card-head">
                            <div class="ta-card-paciente text-truncate">{{ $documento->nombre_original }}</div>
                            <span class="badge {{ $documento->estado->badgeClass() }}">{{ $documento->estado->label() }}</span>
                        </div>

                        <dl class="ta-card-body">
                            <div class="ta-dato">
                                <dt>Cargado</dt>
                                <dd>{{ $documento->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Usuario</dt>
                                <dd>{{ $documento->usuario?->name ?? '—' }}</dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Progreso</dt>
                                <dd>{{ $documento->progreso }}%</dd>
                            </div>
                            <div class="ta-dato">
                                <dt>Creditos</dt>
                                <dd>{{ $documento->usage_credits !== null ? number_format((float) $documento->usage_credits, 2, ',', '.') : '—' }}</dd>
                            </div>
                        </dl>

                        <div class="px-3 pb-2">
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: {{ min(100, max(0, $documento->progreso)) }}%"
                                    aria-valuenow="{{ $documento->progreso }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="small text-muted mt-1">{{ $documento->progreso_mensaje ?? '—' }}</div>
                            @if ($documento->error)
                                <div class="small text-danger mt-1">{{ \Illuminate\Support\Str::limit($documento->error, 160) }}</div>
                            @endif
                        </div>

                        <div class="ta-card-foot">
                            <a href="{{ route('documentos_llamaparse.ver', $documento) }}"
                                class="btn btn-sm btn-outline-primary flex-fill ta-accion">
                                <i class="fa-regular fa-eye me-1"></i>Ver detalle
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="ta-vacio">
                        <i class="fa-solid fa-cloud-arrow-up fa-2x mb-2 text-secondary opacity-50"></i>
                        <div>No hay documentos LlamaParse cargados.</div>
                    </div>
                @endforelse
            </div>

            <div class="ta-solo-escritorio">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Archivo</th>
                                <th>Usuario</th>
                                <th>Cargado</th>
                                <th>Estado</th>
                                <th>Progreso</th>
                                <th>Creditos</th>
                                <th class="text-end pe-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documentos as $documento)
                                <tr wire:key="llamaparse-{{ $documento->id }}">
                                    <td class="fw-semibold text-truncate" style="max-width: 22rem;">
                                        {{ $documento->nombre_original }}
                                    </td>
                                    <td>{{ $documento->usuario?->name ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $documento->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ $documento->estado->badgeClass() }}">
                                            {{ $documento->estado->label() }}
                                        </span>
                                        @if ($documento->error)
                                            <div class="small text-danger text-truncate" style="max-width: 18rem;" title="{{ $documento->error }}">
                                                {{ \Illuminate\Support\Str::limit($documento->error, 80) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td style="min-width: 12rem;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar" role="progressbar"
                                                    style="width: {{ min(100, max(0, $documento->progreso)) }}%"
                                                    aria-valuenow="{{ $documento->progreso }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="small text-muted text-nowrap">{{ $documento->progreso }}%</span>
                                        </div>
                                        <div class="small text-muted text-truncate" style="max-width: 18rem;">
                                            {{ $documento->progreso_mensaje ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        {{ $documento->usage_credits !== null ? number_format((float) $documento->usage_credits, 2, ',', '.') : '—' }}
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('documentos_llamaparse.ver', $documento) }}"
                                            class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-cloud-arrow-up fa-2x mb-2 text-secondary opacity-50"></i>
                                        <div>No hay documentos LlamaParse cargados.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($documentos->hasPages())
                <div class="card-footer py-2 ta-paginacion">
                    {{ $documentos->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
