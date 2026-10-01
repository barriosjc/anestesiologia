<div class="terceros-anestesia-form container-fluid px-2 px-md-4 mt-3">
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <h5 class="m-0 font-weight-bold text-primary fs-6">
                    <i class="fa-solid fa-notes-medical me-2"></i>
                    {{ $tercero_id ? 'Editar registro de anestesia' : 'Nuevo registro de anestesia' }}
                </h5>
                <a href="{{ route('terceros_anestesia.index') }}" class="btn btn-outline-secondary btn-sm ta-accion">
                    <i class="fa-solid fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>

        <form wire:submit="guardar" novalidate>
            <div class="card-body">
                @include('utiles.errors')

                <div class="row g-3">

                    {{-- Fecha / Hora / Paciente / Estado --}}
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label small fw-bold" for="input_fecha">Fecha <span class="text-danger">*</span></label>
                        <input
                            type="date"
                            wire:model="fecha"
                            id="input_fecha"
                            class="form-control @error('fecha') is-invalid @enderror"
                        />
                        @error('fecha') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label small fw-bold" for="input_hora">Hora</label>
                        <input
                            type="time"
                            wire:model="hora"
                            id="input_hora"
                            class="form-control @error('hora') is-invalid @enderror"
                        />
                        @error('hora') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label small fw-bold" for="input_paciente">Paciente <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            wire:model="paciente"
                            id="input_paciente"
                            placeholder="Nombre y apellido"
                            autocomplete="off"
                            enterkeyhint="next"
                            class="form-control @error('paciente') is-invalid @enderror"
                        />
                        @error('paciente') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-lg-2">
                        <label class="form-label small fw-bold" for="sel_estado">Estado <span class="text-danger">*</span></label>
                        <select wire:model="estado" id="sel_estado" class="form-select @error('estado') is-invalid @enderror">
                            @foreach ($estados as $e)
                                <option value="{{ $e->value }}">{{ $e->label() }}</option>
                            @endforeach
                        </select>
                        @error('estado') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- Cobertura -> Choices.js (define los procedimientos disponibles) --}}
                    <div class="col-12 col-lg-6">
                        <label class="form-label small fw-bold" for="sel_cobertura">Cobertura</label>
                        <div wire:ignore x-data="{
                                init() {
                                    initChoices(this.$refs.coberturaSelect, {
                                        placeholderValue: 'Seleccione una cobertura...',
                                        searchPlaceholderValue: 'Buscar cobertura...',
                                        onChange: (value) => {
                                            $wire.set('cobertura_id', value ? parseInt(value, 10) : null);
                                        },
                                    });
                                }
                            }">
                            <select x-ref="coberturaSelect" id="sel_cobertura" class="form-select">
                                <option value="">Seleccione una cobertura...</option>
                                @foreach ($coberturas as $cob)
                                    <option value="{{ $cob->id }}" @selected($cobertura_id == $cob->id)>{{ $cob->sigla ?: $cob->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('cobertura_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- Anestesiólogo (Profesionales) -> Choices.js --}}
                    <div class="col-12 col-lg-6">
                        <label class="form-label small fw-bold" for="sel_profesional">Anestesiólogo (Profesional)</label>
                        <div wire:ignore x-data="{
                                init() {
                                    initChoices(this.$refs.profesionalSelect, {
                                        placeholderValue: 'Seleccione un anestesiólogo...',
                                        searchPlaceholderValue: 'Buscar médico...',
                                        onChange: (value) => {
                                            $wire.set('profesional_id', value ? parseInt(value, 10) : null);
                                        },
                                    });
                                }
                            }">
                            <select x-ref="profesionalSelect" id="sel_profesional" class="form-select">
                                <option value="">Seleccione un anestesiólogo...</option>
                                @foreach ($profesionales as $prof)
                                    <option value="{{ $prof->id }}" @selected($profesional_id == $prof->id)>{{ $prof->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('profesional_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- Procedimiento (Nomenclador) -> Choices.js
                         Depende de la cobertura: al cambiarla se recarga el listado
                         desde el evento 'nomencladores-actualizados'. --}}
                    <div class="col-12 col-lg-6">
                        <label class="form-label small fw-bold" for="sel_nomenclador">Procedimiento (Nomenclador)</label>
                        <div wire:ignore x-data="{
                                choices: null,
                                firma: '',
                                init() {
                                    this.firma = this.calcularFirma();
                                    this.choices = initChoices(this.$refs.nomencladorSelect, {
                                        placeholderValue: 'Seleccione un procedimiento...',
                                        searchPlaceholderValue: 'Buscar procedimiento...',
                                        onChange: (value) => {
                                            $wire.set('nomenclador_id', value ? parseInt(value, 10) : null);
                                        },
                                    });
                                },
                                calcularFirma() {
                                    return Array.from(this.$refs.nomencladorSelect.options)
                                        .map((opcion) => opcion.value)
                                        .filter((valor) => valor !== '')
                                        .map(Number)
                                        .sort((a, b) => a - b)
                                        .join(',');
                                },
                                actualizar(opciones, firma, seleccionado) {
                                    if (!this.choices) return;
                                    // Controlar antes de recargar: si el listado es el
                                    // mismo nomenclador, no volver a recargar el select.
                                    if (firma === this.firma) return;
                                    this.firma = firma;
                                    const items = [
                                        { value: '', label: 'Seleccione un procedimiento...', placeholder: true, selected: !seleccionado },
                                        ...opciones.map((opcion) => ({
                                            value: opcion.value,
                                            label: opcion.label,
                                            selected: seleccionado !== null && String(seleccionado) === String(opcion.value),
                                        })),
                                    ];
                                    this.choices.setChoices(items, 'value', 'label', true);
                                }
                            }"
                            x-on:nomencladores-actualizados.window="actualizar($event.detail.opciones, $event.detail.firma, $event.detail.seleccionado)">
                            <select x-ref="nomencladorSelect" id="sel_nomenclador" class="form-select">
                                <option value="">Seleccione un procedimiento...</option>
                                @foreach ($nomencladores as $nom)
                                    <option value="{{ $nom->id }}" @selected($nomenclador_id == $nom->id)>[{{ $nom->codigo }}] {{ $nom->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('nomenclador_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- Urgencia --}}
                    <div class="col-6 col-lg-3">
                        <label class="form-label small fw-bold d-block" id="lbl_urgencia">Urgencia</label>
                        <div class="btn-group w-100" role="group" aria-labelledby="lbl_urgencia">
                            <input type="radio" class="btn-check" wire:model="urgencia" name="urgencia" id="urgencia_0" value="0" autocomplete="off" />
                            <label class="btn btn-outline-primary" for="urgencia_0">No</label>
                            <input type="radio" class="btn-check" wire:model="urgencia" name="urgencia" id="urgencia_1" value="1" autocomplete="off" />
                            <label class="btn btn-outline-primary" for="urgencia_1">Sí</label>
                        </div>
                        @error('urgencia') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- Medinexus / Pasado a sistema --}}
                    <div class="col-6 col-lg-3">
                        <label class="form-label small fw-bold d-block" id="lbl_pasado">Pasado a sistema</label>
                        <div class="btn-group w-100" role="group" aria-labelledby="lbl_pasado">
                            <input type="radio" class="btn-check" wire:model="pasado_sistema" name="pasado_sistema" id="pasado_sistema_0" value="0" autocomplete="off" />
                            <label class="btn btn-outline-primary" for="pasado_sistema_0">No</label>
                            <input type="radio" class="btn-check" wire:model="pasado_sistema" name="pasado_sistema" id="pasado_sistema_1" value="1" autocomplete="off" />
                            <label class="btn btn-outline-primary" for="pasado_sistema_1">Sí</label>
                        </div>
                        @error('pasado_sistema') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- Observaciones --}}
                    <div class="col-12">
                        <label class="form-label small fw-bold" for="input_observaciones">Observaciones</label>
                        <textarea
                            wire:model="observaciones"
                            id="input_observaciones"
                            rows="4"
                            maxlength="2000"
                            placeholder="Observaciones clínicas o administrativas..."
                            class="form-control @error('observaciones') is-invalid @enderror"
                        ></textarea>
                        @error('observaciones') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                </div>
            </div>

            {{-- Pie fijo: en celular el botón Guardar queda alcanzable sin
                 scrollear hasta el final del formulario. --}}
            <div class="card-footer ta-form-actions bg-light">
                <div class="d-flex flex-column-reverse flex-sm-row gap-2 justify-content-sm-end">
                    <a href="{{ route('terceros_anestesia.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-xmark me-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>