<div class="box box-info padding-1">
    <div class="box-body">
        
        <div class="form-group">
            {{ Form::label('razon_rosial') }}
            {{ Form::text('razon_rosial', $empresa->razon_rosial, ['class' => 'form-control' . ($errors->has('razon_rosial') ? ' is-invalid' : ''), 'placeholder' => 'Razon Rosial']) }}
            {!! $errors->first('razon_rosial', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('contacto') }}
            {{ Form::text('contacto', $empresa->contacto, ['class' => 'form-control' . ($errors->has('contacto') ? ' is-invalid' : ''), 'placeholder' => 'Contacto']) }}
            {!! $errors->first('contacto', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('telefono') }}
            {{ Form::text('telefono', $empresa->telefono, ['class' => 'form-control' . ($errors->has('telefono') ? ' is-invalid' : ''), 'placeholder' => 'Telefono']) }}
            {!! $errors->first('telefono', '<div class="invalid-feedback">:message</div>') !!}
        </div>

    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>