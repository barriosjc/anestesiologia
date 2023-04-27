<div class="box box-info padding-1">
    <div class="box-body">
        
        <div class="form-group">
            {{ Form::label('descripcion') }}
            {{ Form::text('descripcion', $periodosMensual->descripcion, ['class' => 'form-control' . ($errors->has('descripcion') ? ' is-invalid' : ''), 'placeholder' => 'Descripcion']) }}
            {!! $errors->first('descripcion', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('desde') }}
            {{ Form::text('desde', $periodosMensual->desde, ['class' => 'form-control' . ($errors->has('desde') ? ' is-invalid' : ''), 'placeholder' => 'Desde']) }}
            {!! $errors->first('desde', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('hasta') }}
            {{ Form::text('hasta', $periodosMensual->hasta, ['class' => 'form-control' . ($errors->has('hasta') ? ' is-invalid' : ''), 'placeholder' => 'Hasta']) }}
            {!! $errors->first('hasta', '<div class="invalid-feedback">:message</div>') !!}
        </div>

    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>