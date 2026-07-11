<section class="content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="card-title">{{ $parte_id ? "Modificar Parte, Nro: {$parte_id}" : 'Crear Parte' }}</span>
                    <div>
                        <a href="{{ route('partes_cab.filtrar') }}" class="btn btn-info btn-sm" data-placement="left">
                            Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success py-2">{{ session('success') }}</div>
                    @endif

                    <form wire:submit="save" novalidate>
                        <input type="hidden" wire:model="parte_id">

                        <div class="row gx-3 mb-3">
                            <div class="col-md-3">
                                <label class="small mb-1">Gerenciadora</label>
                                <select wire:model="gerenciadora_id" class="form-select" required
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Debe seleccionar una gerenciadora."
                                    x-data x-init="new bootstrap.Tooltip($el)">
                                    <option value=""> -- Seleccione --</option>
                                    @foreach ($gerenciadoras as $data)
                                        <option value="{{ $data->id }}">{{ $data->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('gerenciadora_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1">Centro</label>
                                <select wire:model="centro_id" class="form-select" required>
                                    <option value=""> -- Seleccione --</option>
                                    @foreach ($centros as $data)
                                        <option value="{{ $data->id }}">{{ $data->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('centro_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1" for="fec_prestacion">Fecha cirugía inicio</label>
                                <input wire:model="fec_prestacion" class="form-control" type="datetime-local" required />
                                @error('fec_prestacion') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1" for="fec_prestacion_fin">Fecha cirugía fin</label>
                                <input wire:model="fec_prestacion_fin" class="form-control" type="datetime-local" required />
                                @error('fec_prestacion_fin') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-3">
                                <label class="small mb-1" for="dni">DNI</label>
                                <div class="input-group">
                                    <input wire:model="dni" class="form-control" type="text" placeholder="dni" required />
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button" wire:click="searchPatient">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('dni') <span class="text-danger small">{{ $message }}</span> @enderror
                                @if ($searchMessage)
                                    <div class="alert alert-{{ $searchMessageType }} py-1 mt-1 small">{{ $searchMessage }}</div>
                                @endif
                            </div>
                            <div class="col-md-5">
                                <label class="small mb-1" for="nombre">Nombre y apellido/a</label>
                                <input wire:model="nombre" class="form-control" placeholder="Ingrese su nombre y apellido" required />
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1" for="fec_nacimiento">Fec. Nac.</label>
                                <input wire:model="fec_nacimiento" class="form-control" type="date" required />
                                @error('fec_nacimiento') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label for="cobertura_id" class="small mb-1">Coberturas</label>
                                <div wire:ignore x-data="{
                                        init() {
                                            new TomSelect(this.$refs.coberturaSelect, {
                                                allowEmptyOption: true,
                                                onChange: (value) => { $wire.set('cobertura_id', value) },
                                            });
                                        }
                                    }">
                                    <select x-ref="coberturaSelect" class="form-select">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($coberturas as $data)
                                            <option value="{{ $data->id }}" @selected($cobertura_id == $data->id)>{{ $data->sigla }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('cobertura_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="profesional_id" class="small mb-1">Profesional</label>
                                <select wire:model="profesional_id" class="form-select" required>
                                    <option value=""> -- Seleccione --</option>
                                    @foreach ($profesionales as $data)
                                        <option value="{{ $data->id }}">{{ $data->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('profesional_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-12">
                                <label class="small mb-1">Observaciones</label>
                                <textarea wire:model="observaciones" class="form-control" rows="4"></textarea>
                            </div>
                        </div>

                        <div class="box-footer mt20">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            @if ($parte_id)
                                <a href="{{ route('partes_det.create', $parte_id) }}" class="btn btn-success">Cargar detalle</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
