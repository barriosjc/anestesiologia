<div class="box box-info padding-1">
    <div class="box-body">
        <div class="row gx-3 mb-3">
            <div class="col-md-3">
                <label class="small mb-1" for="grupo">Grupo</label>
                <input class="form-control" id="grupo" name="grupo" type="text" placeholder="grupo"
                    value="{{ old('grupo', $nom_practicas_estudios->grupo) }}" />
            </div>
            <div class="col-md-3">
                <input type="hidden" name="nivel" value="{{ old('nivel', $nivel) }}">
                <label class="small mb-1" for="nivel">Nivel o Código</label>
                <div class="form-control" id="nivel" name="nivel" type="text" style="background-color:#e9ecef">
                    {{ old('nivel', $nivel) }}</div>
            </div>
        </div>
        <div class="row gx-3 mt-3">
            <div class="col-md-3">
                <label class="small mb-1" for="tipo">Tipo</label>
                <input class="form-control" id="tipo" name="tipo" type="text"
                    placeholder="Ingrese tipo de práctica" value="{{ old('tipo', $nom_practicas_estudios->tipo) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="valor">Valor</label>
                <input class="form-control" id="valor" name="valor" type="text" placeholder="Ingrese valor"
                    value="{{ old('valor', $nom_practicas_estudios->valor) }}" />
            </div>
            <input type="hidden" name="aplica_porcent_adic">
            {{-- <div class="col-md-3">
                <label class="small mb-1" for="aplica_porcent_adic">aplica porcentaje adic</label>
                <input class="form-control" id="aplica_porcent_adic" name="aplica_porcent_adic" type="text"
                    placeholder="" value="{{ old('aplica_porcent_adic', $nom_practicas_estudios->aplica_porcent_adic) }}" />
            </div> --}}
        </div>
    </div>
    <hr />
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
