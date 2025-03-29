<div class="box box-info padding-1">
    <div class="box-body">
        <div class="row gx-3 mb-3">
            <div class="col-md-3">
                <label class="small mb-1" for="tipo">Tipo</label>
                <input class="form-control" id="tipo" name="tipo" type="text" placeholder="tipo"
                    value="{{ old('tipo') }}" />
            </div>
            <div class="col-md-6">
                <label class="small mb-1" for="nombre">Nombre</label>
                <input class="form-control" id="nombre" name="nombre" type="text"
                    placeholder="Ingrese descripción del nomenclador" value="{{ old('nombre') }}" />
            </div>
        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>

