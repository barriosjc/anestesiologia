<div class="card mt-3 p-3 border" x-data="{
        codigo: '',
        descripcion: '',
        porcentaje: $wire.entangle('porcentaje'),
        valorOrig: $wire.entangle('valorOrig'),
        valorTotal: $wire.entangle('valorTotal'),
        totalFormateado() {
            let v = parseFloat(this.valorOrig || 0) * (parseFloat(this.porcentaje || 0) / 100);
            let s = v.toFixed(2).replace('.', ',');
            return s.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },
        recalcular() {
            let val = parseFloat(this.porcentaje);
            if (val > 200) {
                this.porcentaje = 100;
                val = 100;
            }
            let v = parseFloat(this.valorOrig || 0) * (val / 100);
            this.valorTotal = parseFloat(v.toFixed(2));
        }
    }" x-init="if (parseFloat(porcentaje) > 100) { porcentaje = 100; } recalcular()" x-effect="recalcular()">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success py-2">{{ session('success') }}</div>
        @endif

        <form wire:submit="guardar" novalidate>
            <div class="row gx-3 mb-3">
                <div class="col-md-6">
                    <label class="small mb-1" for="coberturaId">Coberturas</label>
                    <select wire:model.live="coberturaId" class="form-select" id="coberturaId">
                        <option value="">-- Seleccione --</option>
                        @foreach ($coberturas as $item)
                            <option value="{{ $item->id }}">{{ $item->sigla }}</option>
                        @endforeach
                    </select>
                    @error('coberturaId') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="row gx-3 mb-3">
                <div class="col-md-2">
                    <label class="small mb-1" for="periodo">Periodo</label>
                    <select wire:model.live="periodo" class="form-select" id="periodo">
                        <option value="">-- Seleccione --</option>
                        @foreach ($periodos as $item)
                            <option value="{{ $item->nombre }}">{{ $item->nombre }}</option>
                        @endforeach
                    </select>
                    @error('periodo') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4">
                    <label class="small mb-1" for="codigo">Procedimiento</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="codigo" x-model="codigo" style="flex: 0 0 30%;"
                            data-bs-placement="top" data-bs-title="Debe ingresar un código de nomenclador con o sin guiones."
                            x-init="new bootstrap.Tooltip($el)" placeholder="código" x-on:focus="codigo = ''">
                        <input type="text" class="form-control" id="descripcion" x-model="descripcion"
                            data-bs-placement="top" data-bs-title="Debe ingresar una descripción de práctica del nomenclador."
                            x-init="new bootstrap.Tooltip($el)" placeholder="descripción" x-on:focus="descripcion = ''">
                        <button type="button" class="btn btn-primary"
                            x-on:click="$wire.buscarNomenclador(codigo, descripcion)"><i
                                class="fa fa-fw fa-search"></i></button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="small mb-1" for="nomenclador_id">Nomenclador</label>
                    <select wire:model.live="nomenclador_id" class="form-select" id="nomenclador_id">
                        @if (count($nomencladorOpciones) > 1)
                            <option value="">-- Seleccione una --</option>
                        @endif
                        @foreach ($nomencladorOpciones as $item)
                            <option value="{{ $item['id'] }}">
                                {{ $item['nivel'] !== null ? $item['nivel'] . ' / ' : '' }}{{ $item['codigo'] }} /
                                {{ $item['descripcion'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('nomenclador_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-1 pt-3">
                    <label class="small mb-1" for="porcentaje">% </label>
                    <input type="text" class="form-control" id="porcentaje" x-model.number="porcentaje" required
                        data-bs-placement="top" data-bs-title="Debe ingresar un valor numérico."
                        x-init="new bootstrap.Tooltip($el)">
                    @error('porcentaje') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-2 pt-3">
                    <label class="small mb-1" for="total">Valor ($)</label>
                    @if ($coberturaId == '75')
                        <input type="text" class="form-control" id="total" x-model.number="valorOrig">
                    @else
                        <label class="form-control bg-light text-muted" id="total" x-text="totalFormateado()"></label>
                    @endif
                    @error('valorTotal') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="row gx-3 mb-3">
                <div class="col-md-12">
                    <label class="small mb-1" for="observaciones">Observaciones</label>
                    <textarea class="form-control" wire:model="observaciones" id="observaciones" rows="3"
                        placeholder="Se recomienda no ingresar prácticas a realizar aquí, solo observaciones."></textarea>
                </div>
            </div>
            <div class="row gx-3 mb-3">
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('presupuestos.cab.print', $presupuestoCabId) }}" target="_blank"
                        class="btn btn-success">{{ __('Imprimir') }}</a>
                </div>
            </div>
        </form>
    </div>
</div>
