<form action="{{ route('presupuestos.cab.filtrar') }}" method="GET">
    <div class="row g-3">
        <!-- Filtro por Centro -->
        <div class="col-md-3">
            <label for="centro" class="form-label">Centro</label>
            <select name="centro" id="centro" class="form-select form-select-sm">
                <option value="">Todos los centros</option>
                @foreach ($centros as $centro)
                    <option value="{{ $centro->id }}" {{ request('centro') == $centro ? 'selected' : '' }}>
                        {{ $centro->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filtro por Nombre -->
        <div class="col-md-3">
            <label for="nombre" class="form-label">Cliente/Paciente</label>
            <input type="text" name="nombre" id="nombre" class="form-control form-control-sm"
                value="{{ request('nombre') }}">
        </div>

        <!-- Filtro por Profesional -->
        <div class="col-md-3">
            <label for="profesional" class="form-label">Profesional</label>
            <select name="profesional" id="profesional" class="form-select form-select-sm">
                <option value="">Todos los profesionales</option>
                @foreach ($profesionales as $profesional)
                    <option value="{{ $profesional }}" {{ request('profesional') == $profesional ? 'selected' : '' }}>
                        {{ $profesional }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filtro por Usuario -->
        <div class="col-md-3">
            <label for="usuario" class="form-label">Usuario</label>
            <select name="usuario" id="usuario" class="form-select form-select-sm">
                <option value="">Todos los usuarios</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario }}" {{ request('usuario') == $usuario ? 'selected' : '' }}>
                        {{ $usuario }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filtro por Fecha Desde -->
        <div class="col-md-3">
            <label for="fecha_desde" class="form-label">Fecha Desde</label>
            <input type="date" name="fecha_desde" id="fecha_desde" class="form-control form-control-sm"
                value="{{ request('fecha_desde') }}">
        </div>

        <!-- Filtro por Fecha Hasta -->
        <div class="col-md-3">
            <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control form-control-sm"
                value="{{ request('fecha_hasta') }}">
        </div>

        <!-- Filtro por Estado -->
        <div class="col-md-3">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" id="estado" class="form-select form-select-sm">
                <option value="">Todos los estados</option>
                @foreach ($estados as $key => $estado)
                    <option value="{{ $key }}" {{ request('estado') == $key ? 'selected' : '' }}>
                        {{ $estado['texto'] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
        </div>
        <!-- Botones de Acción -->
        <div class="form-group col-md-2 d-flex align-items-end">
            <button id="submitInputs" name="submitInputs" class="btn btn-primary btn-sm w-100" type="submit">Filtrar </button>
        </div>
        {{-- <div class="form-group col-md-2 d-flex align-items-end">
            <div class="btn-group w-100" role="group">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
            </div>
        </div> --}}
    </div>
</form>
