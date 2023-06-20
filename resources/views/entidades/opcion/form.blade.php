<style>
    .label-color {
        font-weight: bold; 
        text-align: center; 
        font-size: 1.2em; 
        border: 1px solid grey; 
        padding-left: 40px; 
        padding-right: 40px; 
        border-radius: 5px;"
    }

    .bkg-color {
        background-color: grey;
    }
    .f-color {
        color: red;
    }
</style>

<div class="box box-info padding-1">
    <div class="box-body">

        <div class="form-group">
            {{ Form::label('descripcion') }}
            {{ Form::text('descripcion', $opciones->descripcion, ['class' => 'form-control', 'placeholder' => 'Descripcion']) }}
        </div>
        <div class="form-group">
            {{ Form::label('detalle') }}
            {{ Form::textarea('detalle', $opciones->detalle, ['class' => 'form-control', 'placeholder' => 'Detalle', 'rows' => 3]) }}
        </div>
        <div class="row">
            <div class="col-8">
                <div class="form-group">
                    <div class="mb-3">
                        <label for="formFile" class="form-label">Imagen de la opción</label>
                        <input class="form-control" name="imagen" type="file" id="file_opcion" value="{{$opciones->imagen}}">
                        <h6 class="f-color">"Subir imagen en formato PNG con transparencia. Tamaño: 512 x 152px"<h6>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="" class="col-sm-2 col-form-label"></label>
                            <div class="col">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="habilitada"
                                        name="habilitada" checked>
                                    <label class="form-check-label" for="habilitado">Habilitado
                                        (No/Si)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        {{ Form::label('puntos') }}
                        {{-- {{ Form::text('puntos', $opciones->puntos, ['class' => 'form-control' . ($errors->has('puntos') ? ' is-invalid' : ''), 'placeholder' => 'Puntos']) }} --}}
                        <input type="text" value="{{ $opciones->puntos }}" id="puntos" name="puntos"
                            class="form-control">
                    </div>
                </div>
            </div>
            <div class="col-4">
                <img id="img_opcion" class="img-account-profile mx-auto d-block mt-3 bkg-color" 
                        src="{{ asset(Storage::disk('empresas')->url($opciones->imagen)) }}" alt="" />
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('style') }}
            <div class="input-group">
                <input type="text" value="{{ $opciones->style }}"
                    placeholder="cree el style y luego lo puede copiar para usarlo en el próxima opción a dar de alta"
                    id="xstyle" name="style" class="form-control" aria-describedby="btncopy">
                <button type="button" class="input-group-text btn btn-primary" id="btncopy"><i
                        class="fa-solid fa-clipboard"></i></button>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-2">
                <div class="form-group">
                    {{ Form::label('Color Fuente') }}
                    {{ Form::color('fuente', null, ['class' => 'fuente']) }}
                </div>
            </div>
            <div class="col-2">
                <div class="form-group">
                    {{ Form::label('Color Fondo') }}
                    {{ Form::color('fondo', null, ['class' => 'fondo']) }}
                </div>
            </div>
            <div class="col-4">
                <div class="d-inline">
                    <label for="" class="control-label">Resultado  -->  </label>
                    <label  class="resultado label-color">Hola mundo</label>
                </div>
            </div>
    </div>
    <hr />
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>

    <script>
        window.addEventListener('load', function() {
            // Obtener referencias a los elementos del DOM
            var fuenteInput = document.querySelector('.fuente');
            var fondoInput = document.querySelector('.fondo');
            var resultadoLabel = document.querySelector('.resultado');
            var btnCopiar = document.getElementById('btncopy');

            // Escuchar el evento 'change' en los inputs de color
            fuenteInput.addEventListener('change', actualizarColores);
            fondoInput.addEventListener('change', actualizarColores);

            if( "{{ $opciones->style }}" != "" ){
                resultadoLabel.style = "{{$opciones->style}}"
            }

            function actualizarColores() {
                var styleInput = document.querySelector('#xstyle');
                // Obtener los valores de los inputs de color
                var fuenteColor = fuenteInput.value;
                var fondoColor = fondoInput.value;

                // Aplicar los colores al elemento de resultado
                resultadoLabel.style.color = fuenteColor;
                resultadoLabel.style.backgroundColor = fondoColor;
                styleInput.value = "background-color: " + fondoColor + " !important;color: " + fuenteColor +
                    " !important;"
            }

            btnCopiar.addEventListener('click', copiar);

            function copiar() {
                var styleInput = document.querySelector('#xstyle');
                var input = document.getElementById("myInput");

                styleInput.select();
                styleInput.setSelectionRange(0, 99999); /* Para dispositivos móviles */
                document.execCommand("copy");

                // styleInput.blur();
            }

            document.getElementById('file_opcion').addEventListener('change', function(event) {
                var input = event.target;
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('img_opcion').src = e.target.result;
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            });

        });
    </script>
