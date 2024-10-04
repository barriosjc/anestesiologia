<div class="box box-info padding-1">
    <div class="box-body">
        <div class="row gx-3 mb-3">
            <div class="form-group col-md-3">
                <label class="small mb-1" for="gerenciadora_id">Gerenciadoras</label>
                <select class="form-select form-select-sm" id="gerenciadora_id" name="gerenciadora_id">
                    <option value="">-- Seleccione --</option>
                    @foreach ($gerenciadoras as $item)
                        <option value="{{ $item->id }}"
                            {{ old('gerenciadora_id', $listas->gerenciadora_id) == $item->id ? 'selected' : '' }}>
                            {{ $item->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label class="small mb-1" for="cobertura_id">Coberturas</label>
                <select class="form-select form-select-sm" id="cobertura_id" name="cobertura_id">
                    <option value="">-- Seleccione --</option>
                    @foreach ($coberturas as $item)
                        <option value="{{ $item->id }}"
                            {{ old('cobertura_id', $listas->cobertura_id) == $item->id ? 'selected' : '' }}>
                            {{ $item->sigla }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="small mb-1" for="centro_id">Centros</label>
                <select class="form-select form-select-sm" id="centro_id" name="centro_id">
                    <option value="">-- Seleccione --</option>
                    @foreach ($centros as $item)
                        <option value="{{ $item->id }}"
                            {{ old('centro_id', $listas->centro_id) == $item->id ? 'selected' : '' }}>
                            {{ $item->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label class="small mb-1" for="periodo">Periodo</label>
                <select class="form-select form-select-sm" id="periodo" name="periodo">
                    <option value="">-- Seleccione --</option>
                    @foreach ($periodos as $item)
                        <option
                            value="{{ $item->nombre }}" {{ old('periodo', $listas->periodo) == $item->nombre ? 'selected' : '' }}>
                            {{ $item->nombre }} </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-1">
                <label class="small mb-1" for="grupo">Grupo</label>
                <input type="text" class="form-control form-control-sm" name="grupo"
                    value="{{ old('grupo', $listas->grupo) }}">
            </div>
        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>
