<section class="content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="card-title">
                        {{ $presupuesto_id ? __('Editar') : __('Crear') }} Cabecera del presupuesto
                        @if ($presupuesto_id)
                            ({{ $presupuesto_id }})
                        @endif
                    </span>
                    <a href="{{ route('presupuestos.cab.index') }}" title="Volver">
                        <button class="btn btn-warning btn-sm float-right">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                        </button>
                    </a>
                </div>
                <div class="card-body">
                    @include('utiles.alerts')

                    <form wire:submit="save" novalidate>
                        <input type="hidden" wire:model="presupuesto_id">

                        <div class="row gx-3 mb-3">
                            <div class="col-md-3">
                                <label class="small mb-1">Gerenciadora</label><span class='s-red'>*</span>
                                <select wire:model="gerenciadora_id" class="form-select" required>
                                    <option value=""> -- Seleccione --</option>
                                    @foreach ($gerenciadoras as $data)
                                        <option value="{{ $data->id }}">{{ $data->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('gerenciadora_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="fecha">Fecha</label><span class='s-red'>*</span>
                                <input wire:model="fecha" class="form-control" id="fecha" type="date" required />
                                @error('fecha') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1">Centro</label><span class='s-red'>*</span>
                                <select wire:model="centro_id" class="form-select" required>
                                    <option value=""> -- Seleccione --</option>
                                    @foreach ($centros as $data)
                                        <option value="{{ $data->id }}">{{ $data->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('centro_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1">Profesional</label>
                                <select wire:model="profesional_id" class="form-select">
                                    <option value=""> -- Seleccione --</option>
                                    @foreach ($profesionales as $data)
                                        <option value="{{ $data->id }}">{{ $data->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('profesional_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="valor_dolar">Cotización Uds</label><span class='s-red'>*</span>
                                <input wire:model="valor_dolar" class="form-control" id="valor_dolar" type="number"
                                    step="0.01" min="0" data-bs-toggle="tooltip"
                                    data-bs-title="Para ingresar un valor correcto separar decimales con punto." />
                                @error('valor_dolar') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="nombre">Nombre del paciente</label><span class='s-red'>*</span>
                                <input wire:model="nombre" class="form-control" id="nombre" type="text"
                                    placeholder="Ingrese nombre paciente" required />
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="dni">D.N.I.</label>
                                <input wire:model="dni" class="form-control" id="dni" type="text"
                                    placeholder="Ingrese DNI" />
                                @error('dni') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="fecha_nac">Fecha Nac.</label>
                                <input wire:model="fecha_nac" class="form-control" id="fecha_nac" type="date"
                                    placeholder="Ingrese fecha nac." />
                                @error('fecha_nac') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="small mb-1" for="observaciones">Observaciones</label>
                                <textarea wire:model="observaciones" class="form-control" id="observaciones" rows="3"
                                    placeholder="Se recomienda no ingresar prácticas a realizar aquí, solo observaciones."></textarea>
                                @error('observaciones') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="box-footer mt20">
                            <button type="submit" class="btn btn-primary">{{ __('Guardar e ingresar detalle') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
