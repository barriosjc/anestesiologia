<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<div class="box box-info padding-1">
    <div class="box-body">

        <div class="form-group mb-2">
            {{ Form::label('razon_social') }}
            {{ Form::text('razon_social', $empresa->razon_social, ['class' => 'form-control', 'placeholder' => 'Razón Social']) }}
        </div>
        <div class="form-group mb-2">
            {{ Form::label('contacto') }}
            {{ Form::text('contacto', $empresa->contacto, ['class' => 'form-control', 'placeholder' => 'Contacto']) }}
        </div>
        <div class="form-group mb-2" >
            {{ Form::label('telefono') }}
            {{ Form::text('telefono', $empresa->telefono, ['class' => 'form-control', 'placeholder' => 'Teléfono']) }}
        </div>
        <div class="row  mb-2">
            <div class="col-6">
                <label class="small mb-1" for="uri">Prefijo</label>
                <input class="form-control" id="uri" name="uri" type="uri"
                    placeholder="Ingrese el prefijo web para ingresar al portal"
                    value="{{ old('uri', $empresa->uri) }}" />
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-6">
                <label class="small mb-1" for="email_contacto">Email malining</label>
                <input class="form-control" id="email_contacto" name="email_contacto" type="email_contacto"
                    placeholder="Ingrese el email del from para el envio de emails"
                    value="{{ old('email_contacto', $empresa->email_contacto) }}" />
            </div>
            <div class="col-6">
                <label class="small mb-1" for="email_nombre">Nombre mailing</label>
                <input class="form-control" id="email_nombre" name="email_nombre" type="email_nombre"
                    placeholder="Ingrese el nombre del from que se usará para el envio de emils"
                    value="{{ old('email_nombre', $empresa->email_nombre) }}" />
            </div>
        </div>
        <div class="row  mb-2">
            <div class="col-6">
                <label for="formFile" class="small mb-1">Background Login</label>
                <input class="form-control" type="file" name="login" id="login">
                @if($empresa->login)
                    <p class="small mb-1">Valor actual: {{ $empresa->login }}</p>
                @else
                    <p class="small mb-1">No se ha cargado ningún archivo.</p>
                @endif              
            </div>
            <div class="col-6">
                <label for="formFile" class="small mb-1">Header emitir reconocimiento</label>
                <input class="form-control" type="file" name="emitir_reconocimiento" id="emitir_reconocimiento">
                @if($empresa->emitir_reconocimiento)
                    <p class="small mb-1">Valor actual: {{ $empresa->emitir_reconocimiento }}</p>
                @else
                    <p class="small mb-1">No se ha cargado ningún archivo.</p>
                @endif
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-6">
                <label for="formFile" class="small mb-1">Header listado reconocimientos</label>
                <input class="form-control" type="file" name="listado_reconocimientos" id="listado_reconocimientos">
                @if($empresa->listado_reconocimientos)
                    <p class="small mb-1">Valor actual: {{ $empresa->listado_reconocimientos }}</p>
                @else
                    <p class="small mb-1">No se ha cargado ningún archivo.</p>
                @endif
            </div>

            <div class="col-6">
                <label for="formFile" class="small mb-1">Logo empresa</label>
                <input class="form-control" type="file" name="logo" id="logo">
                @if($empresa->logo)
                    <p class="small mb-1">Valor actual: {{ $empresa->logo }}</p>
                @else
                    <p class="small mb-1">No se ha cargado ningún archivo.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="box-footer">
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
