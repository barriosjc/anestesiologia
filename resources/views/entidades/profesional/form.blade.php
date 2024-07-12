<div class="box box-info padding-1">
    <div class="box-body">
        <div class="row gx-3 mb-3">
            <div class="col-md-6">
                <label class="small mb-1" for="nombre">Nombre y apellido</label>
                <input class="form-control" id="nombre" name="nombre" type="text"
                    placeholder="Ingrese su nombre y apellido" value="{{ old('nombre', $profesionales->nombre) }}" />
            </div>
            <div class="col-md-6">
                <label class="small mb-1" for="matricula">Matrícula</label>
                <input class="form-control" id="matricula" name="matricula" type="text" placeholder="matricula"
                    value="{{ old('matricula', $profesionales->matricula) }}" />
            </div>
        </div>
        <div class="mb-3">
            <label class="small mb-1" for="email">Email </label>
            <input class="form-control" id="email" name="email" type="email" placeholder="Ingrese su email"
                value="{{ old('email', $profesionales->email) }}" />
        </div>
        <!-- Form Row-->
        <div class="row gx-3 mb-3">
            <!-- Form Group (phone number)-->
            <div class="col-md-6">
                <label class="small mb-1" for="telefono">Teléfono</label>
                <input class="form-control" id="telefono" name="telefono" type="text" placeholder="telefono"
                    value="{{ old('telefono', $profesionales->telefono) }}" />
            </div>
            <div class="col-md-3">
                <label for="estado" class="small mb-1">Estado</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="estado" name="estado"
                        {{ $profesionales->estado > 0 ? 'checked' : '' }}>
                    <label class="form-check-label" for="estado">
                        (No/Si)</label>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>

