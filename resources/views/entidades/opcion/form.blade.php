<div class="box box-info padding-1">
    <div class="box-body">

        <div class="form-group">
            {{ Form::label('descripcion') }}
            {{ Form::text('descripcion', $opciones->descripcion, ['class' => 'form-control' . ($errors->has('descripcion') ? ' is-invalid' : ''), 'placeholder' => 'Descripcion']) }}
            {!! $errors->first('descripcion', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('detalle') }}
            {{ Form::textarea('detalle', $opciones->detalle, ['class' => 'form-control' . ($errors->has('detalle') ? ' is-invalid' : ''), 'placeholder' => 'Detalle', 'rows' => 3]) }}
            {!! $errors->first('detalle', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('imagen') }}
            {{ Form::text('imagen', $opciones->imagen, ['class' => 'form-control' . ($errors->has('imagen') ? ' is-invalid' : ''), 'placeholder' => 'Imagen']) }}
            {!! $errors->first('imagen', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="row">
            <div class="col-2">
                <div class="form-group">
                    {{ Form::label('habilitada') }}
                    {{ Form::text('habilitada', $opciones->habilitada, ['class' => 'form-control' . ($errors->has('habilitada') ? ' is-invalid' : ''), 'placeholder' => 'Habilitada']) }}
                    {!! $errors->first('habilitada', '<div class="invalid-feedback">:message</div>') !!}
                </div>
            </div>
            <div class="col-2">
                <div class="form-group">
                    {{ Form::label('puntos') }}
                    {{-- {{ Form::text('puntos', $opciones->puntos, ['class' => 'form-control' . ($errors->has('puntos') ? ' is-invalid' : ''), 'placeholder' => 'Puntos']) }} --}}
                    <input type="text" value="{{$opciones->puntos}}" id="puntos" name="puntos" class="form-control">
                    {!! $errors->first('puntos', '<div class="invalid-feedback">:message</div>') !!}
                </div>
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('style') }}
             <input type="text" value="{{$opciones->style}}" id="xstyle" name="style" class="form-control">
            {!! $errors->first('xstyle', '<div class="invalid-feedback">:message</div>') !!}
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
            <div class="col-1">
            </div>
            <div class="col-2">
                <div class="form-group">
                    {{ Form::label('Resultado') }}
                    {{ Form::label('Hola mundo', null, ['style'=>$opciones->style ,'class' => 'resultado']) }}
                </div>
            </div>
        </div>

    </div>
    <hr />
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>

<script>
    window.addEventListener('load', function() {
        // Obtener referencias a los elementos del DOM
        var fuenteInput = document.querySelector('.fuente');
        var fondoInput = document.querySelector('.fondo');
        var resultadoLabel = document.querySelector('.resultado');


        // Escuchar el evento 'change' en los inputs de color
        fuenteInput.addEventListener('change', actualizarColores);
        fondoInput.addEventListener('change', actualizarColores);

        function actualizarColores() {
            var styleInput = document.querySelector('#xstyle');
            // Obtener los valores de los inputs de color
            var fuenteColor = fuenteInput.value;
            var fondoColor = fondoInput.value;

            // Aplicar los colores al elemento de resultado
            resultadoLabel.style.color = fuenteColor;
            resultadoLabel.style.backgroundColor = fondoColor;
            styleInput.value = "background-color: "+fondoColor+" !important;color: "+fuenteColor+" !important;"

        }

    });
</script>