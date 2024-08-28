<div class="box box-info padding-1">
    <div class="box-body">
        <div class="row gx-3 mb-3">
            <div class="col-md-6">
                <label class="small mb-1" for="nombre">Nombre</label>
                <input class="form-control" id="nombre" name="nombre" type="text"
                    placeholder="Ingrese su nombre y apellido" value="{{ old('nombre', $coberturas->nombre) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="sigla">Sigla </label>
                <input class="form-control" id="sigla" name="sigla" type="text" placeholder="Ingrese su sigla"
                    value="{{ old('sigla', $coberturas->sigla) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="cuit">CUIT</label>
                <input class="form-control" id="cuit" name="cuit" type="text" placeholder="cuit"
                    value="{{ old('cuit', $coberturas->cuit) }}" />
            </div>
        </div>
        <div class="row gx-3 mb-3">
            <div class="col-md-2">
                <label class="small mb-1" for="grupo">Grupo</label>
                <input class="form-control" id="grupo" name="grupo" type="text" placeholder="grupo"
                    value="{{ old('grupo', $coberturas->grupo) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="edad_desde">Edad desde</label>
                <input class="form-control" id="edad_desde" name="edad_desde" type="text" placeholder="edad_desde"
                    value="{{ old('edad_desde', $coberturas->edad_desde) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="edad_hasta">Edad hasta</label>
                <input class="form-control" id="edad_hasta" name="edad_hasta" type="text" placeholder="edad_hasta"
                    value="{{ old('edad_hasta', $coberturas->edad_hasta) }}" />
            </div>
            <div class="col-md-2">
                <label class="small mb-1" for="porcentaje_adic">% adicional</label>
                <input class="form-control" id="porcentaje_adic" name="porcentaje_adic" type="text" placeholder="porcentaje_adic"
                    value="{{ old('porcentaje_adic', $coberturas->porcentaje_adic) }}" />
            </div>
        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>

