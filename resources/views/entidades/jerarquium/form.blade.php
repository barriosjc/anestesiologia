<div class="box box-info padding-1">
    <div class="box-body">
        
        <div class="form-group">
            {{ Form::label('Empresa') }}
            <select name="users_empresas_id" class="form-control" id="users_empresas_id" required>
                <option value=""> --- Select ---</option>
                @foreach ($empresas as $data)
                    <option value="{{ $data->id }}"
                        {{ isset($empresas->id) && $empresas->id == $data->id ? 'selected' : '' }}>
                        {{ $data->razon_social }}</option>
                @endforeach
            </select>

        </div>
        <div class="form-group">
            {{ Form::label('Jefe') }}
            <select name="jefe_user_id" class="form-control" id="jefe_user_id" required>
                <option value=""> --- Select ---</option>
                @foreach ($jefeusers as $data)
                    <option value="{{ $data->id }}"
                        {{ isset($jefeusers->id) && $jefeusers->id == $data->id ? 'selected' : '' }}>
                        {{ $data->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            {{ Form::label('Empleado') }}
            <select name="users_id" class="form-control" id="users_id" required>
                <option value=""> --- Select ---</option>
                @foreach ($users as $data)
                    <option value="{{ $data->id }}"
                        {{ isset($users->id) && $users->id == $data->id ? 'selected' : '' }}>
                        {{ $data->name }}</option>
                @endforeach
            </select>            
        </div>

    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>

