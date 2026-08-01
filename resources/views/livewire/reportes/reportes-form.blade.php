<div class="container-fluid" x-data x-on:reporte-pdf-listo.window="window.open($event.detail.url, '_blank')">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            {{ __('Listados') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit="generar">
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label class="small mb-1" for="cobertura_id">Coberturas</label>
                                <div wire:ignore x-data="{
                                        ts: null,
                                        init() {
                                            this.ts = new TomSelect(this.$refs.coberturaSelect, {
                                                allowEmptyOption: true,
                                                placeholder: '-- Seleccione --',
                                                onChange: (value) => { $wire.set('cobertura_id', value) },
                                            });
                                        }
                                    }">
                                    <select x-ref="coberturaSelect" class="form-select form-select-sm">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($coberturas as $item)
                                            <option value="{{ $item->id }}" @selected($cobertura_id == $item->id)>{{ $item->sigla }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-md-3">
                                <label class="small mb-1" for="centro_id">Centros</label>
                                <select wire:model="centro_id" class="form-select form-select-sm">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($centros as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label class="small mb-1" for="profesional_id">Profesional</label>
                                <select wire:model="profesional_id" class="form-select form-select-sm">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($profesionales as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label class="small mb-1" for="nombre">Paciente</label>
                                <input type="text" wire:model="nombre" class="form-control form-control-sm" placeholder="Nombre del paciente">
                            </div>
                        </div>
                        <div class="row pt-2">
                            <div class="form-group col-md-3">
                                <label class="small mb-1" for="estados">Estados</label>
                                <div wire:ignore x-data="{
                                        ts: null,
                                        init() {
                                            this.ts = new TomSelect(this.$refs.estadoSelect, {
                                                plugins: ['remove_button'],
                                                onChange: (values) => { $wire.set('estados', values) },
                                            });
                                        }
                                    }">
                                    <select x-ref="estadoSelect" class="form-select form-select-sm" multiple>
                                        @foreach ($listaEstados as $item)
                                            <option value="{{ $item->id }}" @selected(in_array($item->id, $estados))>{{ $item->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-2">
                                <label class="small mb-1" for="periodo_gen">Periodo generado</label>
                                <select wire:model="periodo_gen" class="form-select form-select-sm">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($periodos as $item)
                                        <option value="{{ $item->nombre }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="fec_desde">Fec. qx desde</label>
                                <input class="form-control form-control-sm" wire:model="fec_desde" type="date" placeholder="Ingrese fecha desde" />
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="fec_hasta">Fec. qx hasta</label>
                                <input class="form-control form-control-sm" wire:model="fec_hasta" type="date" placeholder="Ingrese fecha hasta" />
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="estado_presupuesto">Estado presupuesto</label>
                                <select wire:model="estado_presupuesto" class="form-select form-select-sm">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($estadosPresupuesto as $key => $texto)
                                        <option value="{{ $key }}">{{ $texto }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row pt-2">
                            <div class="form-group col-md-3">
                                <label class="small mb-1" for="user_id">Usuarios</label>
                                <select wire:model="user_id" class="form-select form-select-sm">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($users as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="fec_desde_adm">Fec. carga desde</label>
                                <input class="form-control form-control-sm" wire:model="fec_desde_adm" type="date" placeholder="Ingrese fecha desde" />
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="fec_hasta_adm">Fec. carga hasta</label>
                                <input class="form-control form-control-sm" wire:model="fec_hasta_adm" type="date" placeholder="Ingrese fecha hasta" />
                            </div>
                        </div>
                        <div class="row pt-2">
                            <div class="form-group col-md-10">
                                <label class="small mb-1" for="reporte_id">Reportes a generar</label>
                                <select wire:model="reporte_id" class="form-select form-select-sm">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($listados as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('reporte_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary btn-sm" type="submit" wire:loading.attr="disabled">
                                    Generar listado
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
