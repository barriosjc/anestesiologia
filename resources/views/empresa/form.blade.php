<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<div class="box box-info padding-1">
    <div class="box-body">

        <div class="form-group">
            {{ Form::label('razon_social') }}
            {{ Form::text('razon_social', $empresa->razon_social, ['class' => 'form-control' . ($errors->has('razon_social') ? ' is-invalid' : ''), 'placeholder' => 'Razón Social']) }}
            {!! $errors->first('razon_social', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('contacto') }}
            {{ Form::text('contacto', $empresa->contacto, ['class' => 'form-control' . ($errors->has('contacto') ? ' is-invalid' : ''), 'placeholder' => 'Contacto']) }}
            {!! $errors->first('contacto', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('telefono') }}
            {{ Form::text('telefono', $empresa->telefono, ['class' => 'form-control' . ($errors->has('telefono') ? ' is-invalid' : ''), 'placeholder' => 'Teléfono']) }}
            {!! $errors->first('telefono', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="row">
            <div class="col-6">
                <label class="small mb-1" for="uri">Prefijo</label>
                <input class="form-control" id="uri" name="uri" type="uri"
                    placeholder="Ingrese el prefijo web para ingresar al portal"
                    value="{{ old('uri', $empresa->uri) }}" />
            </div>
            <div class="col-6">
                <label class="small mb-1" for="logo">Logo</label>
                <input class="form-control" id="logo" name="logo" type="logo"
                    placeholder="Ingrese el nombre del archivo logo de la empresa"
                    value="{{ old('logo', $empresa->logo) }}" />
            </div>
        </div>
        <div class="mb-3">
            <label class="small mb-1">Perfil/es</label>
            <select name="perfil_id[]" class="form-control" id="perfil_id" multiple>
                @foreach ($perfiles as $data)
                    <option value="{{ $data->id }}" {{ in_array($data->id, $roles_empresas) ? 'selected' : '' }}>
                        {{ $data->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>

<script>
    $(document).ready(function() {
        $('#perfil_id').select2({
            theme: 'bootstrap-5'
        });

        // for (let index = 0; index < $.length; index++) {
        //   const element = array[index];

        // }

    });
</script>
