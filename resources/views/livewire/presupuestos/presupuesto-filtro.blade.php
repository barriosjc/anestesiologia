<form wire:submit="aplicar">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="small mb-1" for="centro_id">Centro</label>
            <select wire:model="centro_id" class="form-select form-select-sm" id="centro_id">
                <option value="">Todos los centros</option>
                @foreach ($centros as $item)
                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="small mb-1" for="nombre">Cliente/Paciente</label>
            <input type="text" wire:model="nombre" id="nombre" class="form-control form-control-sm">
        </div>

        <div class="col-md-3">
            <label class="small mb-1" for="profesional_id">Profesional</label>
            <select wire:model="profesional_id" class="form-select form-select-sm" id="profesional_id">
                <option value="">Todos los profesionales</option>
                @foreach ($profesionales as $item)
                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="small mb-1" for="usuario_id">Usuario</label>
            <select wire:model="usuario_id" class="form-select form-select-sm" id="usuario_id">
                <option value="">Todos los usuarios</option>
                @foreach ($usuarios as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row g-3 pt-2">
        <div class="col-md-2">
            <label class="small mb-1" for="fecha_desde">Fecha Desde</label>
            <input type="date" wire:model="fecha_desde" id="fecha_desde" class="form-control form-control-sm">
        </div>

        <div class="col-md-2">
            <label class="small mb-1" for="fecha_hasta">Fecha Hasta</label>
            <input type="date" wire:model="fecha_hasta" id="fecha_hasta" class="form-control form-control-sm">
        </div>

        <div class="col-md-2">
            <label class="small mb-1" for="numero">Nro Presupuesto</label>
            <input type="number" wire:model="numero" id="numero" class="form-control form-control-sm" min="1">
        </div>

        <div class="col-md-3">
            <label class="small mb-1" for="estado">Estado</label>
            <select wire:model="estado" class="form-select form-select-sm" id="estado">
                <option value="">Todos los estados</option>
                @foreach ($estados as $key => $item)
                    <option value="{{ $key }}">{{ $item['texto'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 d-flex align-items-end justify-content-end gap-1 ms-auto">
            <button class="btn btn-primary btn-sm" type="submit">Filtrar</button>
            <button class="btn btn-warning btn-sm" type="button" wire:click="limpiar" x-data
                x-init="new bootstrap.Tooltip($el)" data-bs-placement="top" data-bs-title="Limpiar filtro">
                <i class="fa-solid fa-eraser"></i> Borrar filtros
            </button>
        </div>
    </div>
</form>
