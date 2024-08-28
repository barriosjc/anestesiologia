<div class="box box-info padding-1">
    <div class="box-body">
        <div class="row gx-3 mb-3">
            <div class="col-md-6">
                <label class="small mb-1" for="nombre">Nombre</label>
                <input class="form-control" id="nombre" name="nombre" type="text"
                    placeholder="Ingrese su nombre y apellido" value="{{ old('nombre', $centros->nombre) }}" />
            </div>
            <div class="col-md-6">
                <label class="small mb-1" for="cuit">CUIT</label>
                <input class="form-control" id="cuit" name="cuit" type="text" placeholder="cuit"
                    value="{{ old('cuit', $centros->cuit) }}" />
            </div>
            <div class="col-md-6">
                <label class="small mb-1" for="telefono">Teléfono </label>
                <input class="form-control" id="telefono" name="telefono" type="text" placeholder="Ingrese el teléfono"
                    value="{{ old('telefono', $centros->telefono) }}" />
            </div>
            <div class="col-md-6">
                <label class="small mb-1" for="contacto">Contacto</label>
                <input class="form-control" id="contacto" name="contacto" type="text" placeholder="contacto"
                    value="{{ old('contacto', $centros->contacto) }}" />
            </div>
        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>

