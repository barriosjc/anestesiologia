<div class="box box-info padding-1">
    <div class="box-body">
        <div class="row gx-3 mb-3">
            <div class="col-md-6">
                <label class="small mb-1" for="nombre">Nombre</label>
                <input class="form-control" id="nombre" name="nombre" type="text"
                    placeholder="Ingrese su nombre y apellido" value="{{ old('nombre', $parametros->nombre) }}" />
            </div>
            <div class="col-md-6">
                <label class="small mb-1" for="valor">Valor</label>
                <input class="form-control" id="valor" name="valor" type="text" placeholder="Ingrese un valor"
                    value="{{ old('valor', $parametros->valor) }}" />
            </div>
        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>

