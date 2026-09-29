<div>
    <div class="container-fluid px-4 mt-3">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="fa-solid fa-notes-medical me-2"></i>Reso / Tomo / Terceros con Anestesia
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="busqueda"
                            class="form-control form-control-sm"
                            placeholder="Buscar paciente..."
                            style="min-width: 220px;"
                        />
                        <button wire:click="abrirModalNuevo" class="btn btn-primary btn-sm text-nowrap">
                            <i class="fa-solid fa-plus me-1"></i>Nuevo
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                @include('utiles.alerts')

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0 text-sm">
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
                                    <td>{{ $item->cobertura?->nombre ?? '—' }}</td>
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
                                                <span class="small text-muted d-inline-block text-truncate" style="max-width: 140px; vertical-align: bottom;">
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
                                        <button
                                            type="button"
                                            wire:click="abrirModalEditar({{ $item->id }})"
                                            class="btn btn-sm btn-outline-primary me-1"
                                            title="Editar"
                                        >
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
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

                @if ($registros->hasPages())
                    <div class="card-footer py-2">
                        {{ $registros->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Modal ABM ──────────────────────────────────────────────────────── --}}
    <div class="modal fade" id="tercerosAnestesiaModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalLabel">
                        <i class="fa-solid fa-notes-medical me-2"></i>
                        {{ $modalId ? 'Editar registro de anestesia' : 'Nuevo registro de anestesia' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form wire:submit="guardar" novalidate>
                    <div class="modal-body">
                        <div class="row g-3">

                            {{-- Fecha --}}
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="input_fecha">Fecha <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    wire:model="fecha"
                                    id="input_fecha"
                                    class="form-control form-control-sm @error('fecha') is-invalid @enderror"
                                />
                                @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Hora --}}
                            <div class="col-md-2">
                                <label class="form-label small fw-bold" for="input_hora">Hora</label>
                                <input
                                    type="time"
                                    wire:model="hora"
                                    id="input_hora"
                                    class="form-control form-control-sm @error('hora') is-invalid @enderror"
                                />
                                @error('hora') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Paciente --}}
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="input_paciente">Paciente <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    wire:model="paciente"
                                    id="input_paciente"
                                    placeholder="Nombre y apellido"
                                    class="form-control form-control-sm @error('paciente') is-invalid @enderror"
                                />
                                @error('paciente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Estado --}}
                            <div class="col-md-3">
                                <label class="form-label small fw-bold" for="sel_estado">Estado <span class="text-danger">*</span></label>
                                <select wire:model="estado" id="sel_estado" class="form-select form-select-sm @error('estado') is-invalid @enderror">
                                    @foreach ($estados as $e)
                                        <option value="{{ $e->value }}">{{ $e->label() }}</option>
                                    @endforeach
                                </select>
                                @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Procedimiento (Nomenclador) -> Choices.js --}}
                            <div class="col-md-6" wire:ignore>
                                <label class="form-label small fw-bold" for="sel_nomenclador">Procedimiento (Nomenclador)</label>
                                <select id="sel_nomenclador" class="form-select form-select-sm">
                                    <option value="">Seleccione un procedimiento...</option>
                                    @foreach ($nomencladores as $nom)
                                        <option value="{{ $nom->id }}">[{{ $nom->codigo }}] {{ $nom->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Anestesiólogo (Profesionales) -> Choices.js --}}
                            <div class="col-md-6" wire:ignore>
                                <label class="form-label small fw-bold" for="sel_profesional">Anestesiólogo (Profesional)</label>
                                <select id="sel_profesional" class="form-select form-select-sm">
                                    <option value="">Seleccione un anestesiólogo...</option>
                                    @foreach ($profesionales as $prof)
                                        <option value="{{ $prof->id }}">{{ $prof->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Cobertura -> Choices.js --}}
                            <div class="col-md-6" wire:ignore>
                                <label class="form-label small fw-bold" for="sel_cobertura">Cobertura</label>
                                <select id="sel_cobertura" class="form-select form-select-sm">
                                    <option value="">Seleccione una cobertura...</option>
                                    @foreach ($coberturas as $cob)
                                        <option value="{{ $cob->id }}">{{ $cob->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Urgencia --}}
                            <div class="col-md-3">
                                <label class="form-label small fw-bold d-block">Urgencia</label>
                                <div class="btn-group btn-group-sm w-100" role="group" aria-label="Urgencia">
                                    <input type="radio" class="btn-check" wire:model="urgencia" name="urgencia" id="urgencia_0" value="0" autocomplete="off" />
                                    <label class="btn btn-outline-primary" for="urgencia_0">No</label>
                                    <input type="radio" class="btn-check" wire:model="urgencia" name="urgencia" id="urgencia_1" value="1" autocomplete="off" />
                                    <label class="btn btn-outline-primary" for="urgencia_1">Sí</label>
                                </div>
                            </div>

                            {{-- Medinexus / Pasado a sistema --}}
                            <div class="col-md-3">
                                <label class="form-label small fw-bold d-block">Pasado a sistema (Medinexus)</label>
                                <div class="btn-group btn-group-sm w-100" role="group" aria-label="Pasado a sistema">
                                    <input type="radio" class="btn-check" wire:model="pasado_sistema" name="pasado_sistema" id="pasado_sistema_0" value="0" autocomplete="off" />
                                    <label class="btn btn-outline-primary" for="pasado_sistema_0">No</label>
                                    <input type="radio" class="btn-check" wire:model="pasado_sistema" name="pasado_sistema" id="pasado_sistema_1" value="1" autocomplete="off" />
                                    <label class="btn btn-outline-primary" for="pasado_sistema_1">Sí</label>
                                </div>
                            </div>

                            {{-- Observaciones --}}
                            <div class="col-12">
                                <label class="form-label small fw-bold" for="input_observaciones">Observaciones</label>
                                <textarea
                                    wire:model="observaciones"
                                    id="input_observaciones"
                                    rows="4"
                                    placeholder="Observaciones clínicas o administrativas..."
                                    class="form-control form-control-sm @error('observaciones') is-invalid @enderror"
                                ></textarea>
                                @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            let choiceNomenclador = null;
            let choiceProfesional = null;
            let choiceCobertura = null;

            function setupChoices() {
                const elNom = document.getElementById('sel_nomenclador');
                const elProf = document.getElementById('sel_profesional');
                const elCob = document.getElementById('sel_cobertura');

                if (elNom && !choiceNomenclador) {
                    choiceNomenclador = initChoices(elNom, {
                        searchEnabled: true,
                        position: 'bottom',
                        placeholderValue: 'Seleccione un procedimiento...',
                        searchPlaceholderValue: 'Buscar procedimiento...',
                        onChange: (val) => {
                            @this.set('nomenclador_id', val ? parseInt(val, 10) : null);
                        }
                    });
                }

                if (elProf && !choiceProfesional) {
                    choiceProfesional = initChoices(elProf, {
                        searchEnabled: true,
                        position: 'bottom',
                        placeholderValue: 'Seleccione un anestesiólogo...',
                        searchPlaceholderValue: 'Buscar médico...',
                        onChange: (val) => {
                            @this.set('profesional_id', val ? parseInt(val, 10) : null);
                        }
                    });
                }

                if (elCob && !choiceCobertura) {
                    choiceCobertura = initChoices(elCob, {
                        searchEnabled: true,
                        position: 'bottom',
                        placeholderValue: 'Seleccione una cobertura...',
                        searchPlaceholderValue: 'Buscar cobertura...',
                        onChange: (val) => {
                            @this.set('cobertura_id', val ? parseInt(val, 10) : null);
                        }
                    });
                }
            }

            const modalEl = document.getElementById('tercerosAnestesiaModal');
            if (modalEl) {
                modalEl.addEventListener('shown.bs.modal', () => {
                    setupChoices();
                });
            }

            Livewire.on('open-modal', (event) => {
                const target = document.getElementById(event.modal);
                if (target) {
                    bootstrap.Modal.getOrCreateInstance(target).show();
                }
            });

            Livewire.on('close-modal', (event) => {
                const target = document.getElementById(event.modal);
                if (target) {
                    bootstrap.Modal.getInstance(target)?.hide();
                }
            });

            Livewire.on('sincronizar-choices', (payload) => {
                const data = Array.isArray(payload) ? payload[0] : payload;
                setupChoices();

                if (choiceNomenclador) {
                    if (data.nomenclador_id) {
                        choiceNomenclador.setChoiceByValue(String(data.nomenclador_id));
                    } else {
                        resetChoices(choiceNomenclador);
                    }
                }

                if (choiceProfesional) {
                    if (data.profesional_id) {
                        choiceProfesional.setChoiceByValue(String(data.profesional_id));
                    } else {
                        resetChoices(choiceProfesional);
                    }
                }

                if (choiceCobertura) {
                    if (data.cobertura_id) {
                        choiceCobertura.setChoiceByValue(String(data.cobertura_id));
                    } else {
                        resetChoices(choiceCobertura);
                    }
                }
            });
        });
    </script>
    @endpush
</div>
