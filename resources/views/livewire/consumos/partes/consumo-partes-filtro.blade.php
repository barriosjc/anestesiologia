<form wire:submit="aplicar">
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
                }" x-on:filtro-limpiado.window="ts.clear()">
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

        <div class="form-group col-md-2">
            <label class="small mb-1" for="profesional_id">Profesional</label>
            <select wire:model="profesional_id" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
                @foreach ($profesionales as $item)
                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2">
            <label class="small mb-1" for="estado_id">Estados</label>
            <div wire:ignore x-data="{
                    ts: null,
                    init() {
                        this.ts = new TomSelect(this.$refs.estadoSelect, {
                            plugins: ['remove_button'],
                            onChange: (values) => { $wire.set('estado_id', values) },
                        });
                    }
                }" x-on:filtro-limpiado.window="ts.clear()">
                <select x-ref="estadoSelect" class="form-select form-select-sm" multiple>
                    @foreach ($estados as $item)
                        <option value="{{ $item->id }}" @selected(in_array($item->id, $estado_id))>{{ $item->descripcion }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <label class="small mb-1" for="nro_parte">Nro parte</label>
            <input class="form-control form-control-sm" wire:model="nro_parte" type="number"
                min="0" step=1 max="999999999" placeholder="número" />
        </div>
    </div>
    <div class="row pt-2">
        <div class="col-md-2">
            <label class="small mb-1" for="fec_desde">Fec. qx desde</label>
            <input class="form-control form-control-sm" wire:model="fec_desde" type="date"
                placeholder="Ingrese fecha desde" />
        </div>
        <div class="col-md-2">
            <label class="small mb-1" for="fec_hasta">Fec. qx hasta</label>
            <input class="form-control form-control-sm" wire:model="fec_hasta" type="date"
                placeholder="Ingrese fecha hasta" />
        </div>
        <div class="form-group col-md-2">
            <label class="small mb-1" for="nombre">Paciente</label>
            <input type="text" wire:model="nombre" class="form-control form-control-sm" placeholder="Nombre del paciente">
        </div>
        <div class="col-md-2">
            <label class="small mb-1" for="fec_desde_adm">Fec. carga desde</label>
            <input class="form-control form-control-sm" wire:model="fec_desde_adm" type="date"
                placeholder="Ingrese fecha desde" />
        </div>
        <div class="col-md-2">
            <label class="small mb-1" for="fec_hasta_adm">Fec. carga hasta</label>
            <input class="form-control form-control-sm" wire:model="fec_hasta_adm" type="date"
                placeholder="Ingrese fecha hasta" />
        </div>
        <div class="form-group col-md-2 d-flex align-items-end justify-content-end gap-1">
            <button class="btn btn-primary btn-sm w-100" type="submit" x-data
                x-init="new bootstrap.Tooltip($el)" data-bs-placement="top" data-bs-title="Aplicar el filtro ingresado">Filtrar
                partes</button>
            <button class="btn btn-warning btn-sm" type="button" wire:click="limpiar" x-data
                x-init="new bootstrap.Tooltip($el)" data-bs-placement="top" data-bs-title="Limpiar filtro">
                <i class="fa-solid fa-eraser"></i>
            </button>
        </div>
    </div>
</form>
