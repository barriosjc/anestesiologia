<div class="box box-info padding-1">
    <div class="box-body">
        
        <div class="form-group">
            {{ Form::label('descripcion') }}
            {{ Form::text('descripcion', $opciones->descripcion, ['class' => 'form-control' . ($errors->has('descripcion') ? ' is-invalid' : ''), 'placeholder' => 'Descripcion']) }}
            {!! $errors->first('descripcion', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('detalle') }}
            {{ Form::text('detalle', $opciones->detalle, ['class' => 'form-control' . ($errors->has('detalle') ? ' is-invalid' : ''), 'placeholder' => 'Detalle']) }}
            {!! $errors->first('detalle', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('imagen') }}
            {{ Form::text('imagen', $opciones->imagen, ['class' => 'form-control' . ($errors->has('imagen') ? ' is-invalid' : ''), 'placeholder' => 'Imagen']) }}
            {!! $errors->first('imagen', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('style') }}
            {{ Form::text('style', $opciones->style, ['class' => 'form-control' . ($errors->has('style') ? ' is-invalid' : ''), 'placeholder' => 'Style']) }}
            {!! $errors->first('style', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('habilitada') }}
            {{ Form::text('habilitada', $opciones->habilitada, ['class' => 'form-control' . ($errors->has('habilitada') ? ' is-invalid' : ''), 'placeholder' => 'Habilitada']) }}
            {!! $errors->first('habilitada', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('puntos') }}
            {{ Form::text('puntos', $opciones->puntos, ['class' => 'form-control' . ($errors->has('puntos') ? ' is-invalid' : ''), 'placeholder' => 'Puntos']) }}
            {!! $errors->first('puntos', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('puntos_min') }}
            {{ Form::text('puntos_min', $opciones->puntos_min, ['class' => 'form-control' . ($errors->has('puntos_min') ? ' is-invalid' : ''), 'placeholder' => 'Puntos Min']) }}
            {!! $errors->first('puntos_min', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('puntos_max') }}
            {{ Form::text('puntos_max', $opciones->puntos_max, ['class' => 'form-control' . ($errors->has('puntos_max') ? ' is-invalid' : ''), 'placeholder' => 'Puntos Max']) }}
            {!! $errors->first('puntos_max', '<div class="invalid-feedback">:message</div>') !!}
        </div>

    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>