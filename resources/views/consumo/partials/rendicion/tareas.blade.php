<div class="row align-items-end">
    <div id="periodo_asig" class="form-group col-md-2">
        <label class="small mb-1" for="periodo">Periodo de rendición</label>
        <select class="form-select form-select-sm" id="periodo" name="periodo">
            <option value="">-- Seleccione --</option>
            @foreach ($periodos as $item)
                <option value="{{ $item->nombre }}">
                    {{ $item->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <button type="button" id="generate_rendicion_btn" class="btn btn-primary btn-sm">
            {{ __('Generar rendición') }}
        </button>
    </div>
</div>
<Hr>
<div class="row align-items-end pt-2">
    <div class="form-group col-md-2">
        <label class="small mb-1" for="estadoCambio">Cambio de estado</label>
        <select class="form-select form-select-sm" id="estadoCambio" name="estadoCambio">
            <option value="">-- Seleccione --</option>
            @foreach ($estados as $item)
                <option value="{{ $item->id }}">
                    {{ $item->descripcion }}
                </option>
            @endforeach
        </select>
    </div>
    <div id="div_refac" class="form-group col-md-2" style="display: none">
        <label class="small mb-1" for="periodo_refac">Periodo a refacturar</label>
        <select class="form-select form-select-sm" id="periodo_refac" name="periodo_refac">
            <option value="">-- Seleccione --</option>
            @foreach ($periodos as $item)
                <option value="{{ $item->nombre }}">
                    {{ $item->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div id="div_refac2" class="form-group col-md-3" style="display: none">
        <label class="small mb-1" for="obs_refac">Observaciones</label>
        <textarea class="form-control form-control-sm" id="obs_refac" name="obs_refac" rows="3"
            placeholder="Por que pasa a refacturar? "></textarea>
    </div>
    <div class="col-md-2">
        <button type="button" id="cambio_estados_btn" class="btn btn-info btn-sm">
            {{ __('Cambiar estados') }}
        </button>
    </div>
</div>
<Hr>
<div class="row align-items-end pt-2">
    <div id="div_revalorizar" class="form-group col-md-2">
        <label class="small mb-1" for="periodo_refac">Periodo a revalorizar</label>
        <select class="form-select form-select-sm" id="periodo_revalorizar" name="periodo_revalorizar">
            <option value="">-- Seleccione --</option>
            @foreach ($periodos as $item)
                <option value="{{ $item->nombre }}">
                    {{ $item->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <button type="button" id="revalorizar_btn" class="btn btn-warning btn-sm" onclick="revalorizarPartesJS()">
            {{ __('Revaloriazar partes') }}
        </button>
    </div>
</div>
</form>
<Hr>
<div class="row align-items-end pt-2">
    <div class="form-group col-md-2">
        <label class="small mb-1" for="estadoAgregar">Agregar nuevo consumo</label>
        <select class="form-select form-select-sm" id="estadoAgregar" name="estadoAgregar">
            <option value="">-- Seleccione --</option>
            @foreach ($estados as $item)
                <option value="{{ $item->id }}">
                    {{ $item->descripcion }}
                </option>
            @endforeach
        </select>
    </div>
    <div id="div_refac" class="form-group col-md-2">
        <label class="small mb-1" for="periodoAgregar">Periodo</label>
        <select class="form-select form-select-sm" id="periodoAgregar" name="periodoAgregar">
            <option value="">-- Seleccione --</option>
            @foreach ($periodos as $item)
                <option value="{{ $item->nombre }}">
                    {{ $item->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-5">
        <label class="small mb-1" for="obsAgregar">Observaciones</label>
        <textarea class="form-control form-control-sm" id="obsAgregar" name="obsAgregar" rows="1"
            placeholder="Por que agrega neuvo consumo? "></textarea>
    </div>
    <div class="form-group col-md-2">
        <label class="small mb-1" for="valorAgregar">Valor ($)</label>
        <input type="text" class="form-control form-control-sm" id="valorAgregar" name="valorAgregar">
    </div>
    <div class="col-md-1">
        <button type="button" id="btnAgregar" class="btn btn-info btn-sm">
            {{ __('Guardar') }}
        </button>
    </div>
</div>
<Hr>
<div class="row align-items-end pt-2">
    <div class="form-group col-md-2">
        <label class="small mb-1" for="estadoAgregaryDiff">Nuevo consumo y diferencia</label>
        <select class="form-select form-select-sm" id="estadoAgregaryDiff" name="estadoAgregaryDiff">
            <option value="">-- Seleccione --</option>
            @foreach ($estados as $item)
                <option value="{{ $item->id }}">
                    {{ $item->descripcion }}
                </option>
            @endforeach
        </select>
    </div>
    <div id="div_refac" class="form-group col-md-2">
        <label class="small mb-1" for="periodoAgregaryDiff">Periodo</label>
        <select class="form-select form-select-sm" id="periodoAgregaryDiff" name="periodoAgregaryDiff">
            <option value="">-- Seleccione --</option>
            @foreach ($periodos as $item)
                <option value="{{ $item->nombre }}">
                    {{ $item->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4">
        <label class="small mb-1" for="obsAgregaryDiff">Observaciones</label>
        <textarea class="form-control form-control-sm" id="obsAgregaryDiff" name="obsAgregaryDiff" rows="1"
            placeholder="Por que agrega neuvo consumo? "></textarea>
    </div>
    <div id="diff_refac" class="form-group col-md-1">
        <div>
            <input type="radio" id="refacturarydiff" name="refacturaryDiff" value="refacturar" checked>
            <label class="small mb-1" for="refacturar">Refacturar</label>
        </div>
        <div>
            <input type="radio" id="auditoriaydiff" name="refacturaryDiff" value="auditoria">
            <label class="small mb-1" for="auditoria">Auditoría</label>
        </div>
    </div>
    <div class="form-group col-md-2">
        <label class="small mb-1" for="valorAgregar">Valor ($)</label>
        <input type="text" class="form-control form-control-sm" id="valorAgregaryDiff" name="valorAgregar">
    </div>
    <div class="col-md-1">
        <button type="button" id="btnAgregaryDiff" class="btn btn-info btn-sm">
            {{ __('Guardar') }}
        </button>
    </div>
</div>  
