<div class="box box-info padding-1">
    <div class="box-body">
        
        <div class="form-group">
            {{ Form::label('users_id') }}
            {{ Form::text('users_id', $jerarquium->users_id, ['class' => 'form-control' . ($errors->has('users_id') ? ' is-invalid' : ''), 'placeholder' => 'Users Id']) }}
            {!! $errors->first('users_id', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('users_empresas_id') }}
            {{ Form::text('users_empresas_id', $jerarquium->users_empresas_id, ['class' => 'form-control' . ($errors->has('users_empresas_id') ? ' is-invalid' : ''), 'placeholder' => 'Users Empresas Id']) }}
            {!! $errors->first('users_empresas_id', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('user_jefe_id') }}
            {{ Form::text('user_jefe_id', $jerarquium->user_jefe_id, ['class' => 'form-control' . ($errors->has('user_jefe_id') ? ' is-invalid' : ''), 'placeholder' => 'User Jefe Id']) }}
            {!! $errors->first('user_jefe_id', '<div class="invalid-feedback">:message</div>') !!}
        </div>

    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>