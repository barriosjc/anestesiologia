<div class="row">
    <form id="reportForm" action="{{ route('partes_cab.filtrar') }}" method='GET'>
        <div class="row">
            <div class="form-group col-md-3">
                <label class="small mb-1" for="cobertura_id">Coberturas</label>
                <select class="form-select form-select-sm custom-select select2" id="cobertura_id" name="cobertura_id">
                    <option value="">-- Seleccione --</option>
                    @foreach ($coberturas as $item)
                        <option value="{{ $item->id }}"
                            {{ old('cobertura_id', session('p_cobertura_id')) == $item->id ? 'selected' : '' }}>
                            {{ $item->sigla }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group col-md-3">
                <label class="small mb-1" for="centro_id">Centros</label>
                <select class="form-select form-select-sm" id="centro_id" name="centro_id">
                    <option value="">-- Seleccione --</option>
                    @foreach ($centros as $item)
                        <option value="{{ $item->id }}"
                            {{ old('centro_id', session('p_centro_id')) == $item->id ? 'selected' : '' }}>
                            {{ $item->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="small mb-1" for="profesional_id">Profesional</label>
                <select class="form-select form-select-sm" id="profesional_id"
                    name="profesional_id">
                    <option value="">-- Seleccione --</option>
                    @foreach ($profesionales as $item)
                        <option value="{{ $item->id }}"
                            {{ old('profesional_id', session('p_profesional_id')) == $item->id ? 'selected' : '' }}>
                            {{ $item->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label class="small mb-1" for="centro_id">Paciente</label>
                <input type="text" class="form-control form-control-sm"
                    name="nombre" value="{{ old('nombre', session('p_nombre')) }}"
                    placeholder="Nombre del paciente">
            </div>
        </div>
        <div class="row pt-2">
            <div class="form-group col-md-3">
                <label class="small mb-1" for="estado_id">Estados</label>
                <select class="form-select form-select-sm" id="estado_id"
                    name="estado_id">
                    <option value="">-- Seleccione --</option>
                    @foreach ($estados as $item)
                        <option value="{{ $item->id }}"
                            {{ old('estado_id', session('p_estado_id')) == $item->id ? 'selected' : '' }}>
                            {{ $item->descripcion }} </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small mb-1" for="fec_desde">Fec. qx desde</label>
                <input class="form-control form-control-sm" id="fec_desde"
                    name="fec_desde" type="date"
                    placeholder="Ingrese fecha desde"
                    value="{{ old('fec_desde', session('p_fec_desde')) }}" />
            </div>
            <div class="col-md-2">
                <label class="small mb-1" for="fec_hasta">Fec. qx hasta</label>
                <input class="form-control form-control-sm" id="fec_hasta"
                    name="fec_hasta" type="date"
                    placeholder="Ingrese fecha hasta"
                    value="{{ old('fec_hasta', session('p_fec_hasta')) }}" />
            </div>
        </div>
        <div class="row pt-2">
            <div class="form-group col-md-3">
                <label class="small mb-1" for="user_id">Usuarios</label>
                <select class="form-select form-select-sm" id="user_id"
                    name="user_id">
                    <option value="">-- Seleccione --</option>
                    @foreach ($users as $item)
                        <option value="{{ $item->id }}"
                            {{ old('user_id', session('p_user_id')) == $item->id ? 'selected' : '' }}>
                            {{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small mb-1" for="fec_desde_adm">Fec. carga
                    desde</label>
                <input class="form-control form-control-sm" id="fec_desde_adm"
                    name="fec_desde_adm" type="date"
                    placeholder="Ingrese fecha desde"
                    value="{{ old('fec_desde_adm', session('p_fec_desde_adm')) }}" />
            </div>
            <div class="col-md-2">
                <label class="small mb-1" for="fec_hasta_adm">Fec. carga
                    hasta</label>
                <input class="form-control form-control-sm" id="fec_hasta_adm"
                    name="fec_hasta_adm" type="date"
                    placeholder="Ingrese fecha hasta"
                    value="{{ old('fec_hasta_adm', session('p_fec_hasta_adm')) }}" />
            </div>
            <div class="col-md-2">
                <label class="small mb-1" for="nro_parte">Nro parte</label>
                <input class="form-control form-control-sm" id="nro_parte"
                    name="nro_parte" type="number" min="0" step=1
                    max="999999999" placeholder="número"
                    value="{{ old('nro_parte', session('p_nro_parte')) }}" />
            </div>
            <div class="form-group col-md-2 d-flex align-items-end">
                <button id="submitInputs" name="submitInputs" class="btn btn-primary btn-sm"
                    type="submit">Filtrar
                    listado</button>
            </div>
        </div>
    </form>
</div>