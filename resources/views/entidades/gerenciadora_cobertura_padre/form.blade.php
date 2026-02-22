<div class="box box-info padding-1">
    <div class="box-body">
        <div class="row gx-3 mb-3">
            <div class="col-md-4">
                <label class="small mb-1">Gerenciadora</label>
                <select name="gerenciadora_id" class="form-select" id="gerenciadora_id" required>
                    <option value=""> -- Seleccione --</option>
                    @foreach ($gerenciadoras as $data)
                        <option value="{{ $data->id }}" {{old('gerenciadora_id', $gerenciadora_cobertura_padre->gerenciadora_id) == $data->id ? 'selected' : ''}}>    
                            {{ $data->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="small mb-1" for="cobertura_id">Coberturas</label>
                <select class="form-select form-select-sm select2" id="cobertura_id" name="cobertura_id">
                    <option value="">-- Seleccione --</option>
                    @foreach ($coberturas as $data)
                        <option value="{{ $data->id }}" {{old('cobertura_id', $gerenciadora_cobertura_padre->cobertura_id) == $data->id ? 'selected' : ''}}>     
                                {{ $data->sigla }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="nom_padre_id">Padre</label>
                <input class="form-control" id="nom_padre_id" name="nom_padre_id" type="text" placeholder="nom_padre_id"
                    value="{{ old('nom_padre_id', $gerenciadora_cobertura_padre->nom_padre_id) }}" />
            </div>

        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>

