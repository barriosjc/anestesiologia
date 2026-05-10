<div class="box box-info padding-1">
    <div class="box-body">
        <input type="hidden" name="aplica_pocent_adic" value=0>
        <div class="row gx-3 mb-3">
            <div class="col-md-3">
                <label class="small mb-1" for="grupo">Grupo</label>
                <input class="form-control" id="grupo" name="grupo" type="text" placeholder="grupo"
                    value="{{ old('grupo', $nom_practicas_estudios->grupo) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="nivel">nivel</label>
                <input class="form-control" id="nivel" name="nivel" type="text" placeholder="Ingrese nivel"
                    value="{{ old('nivel', $nom_practicas_estudios->nivel) }}" />
            </div>
        </div>
        <div class="row gx-3 mt-3">
            <div class="col-md-3">
                <label class="small mb-1" for="tipo">Tipo (1:$ / 2:un)</label>
                <input class="form-control" id="tipo" name="tipo" type="text"
                    placeholder="Ingrese tipo de práctica" value="{{ old('tipo', $nom_practicas_estudios->tipo) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="valor">Valor</label>
                <input class="form-control" id="valor" name="valor" type="text" placeholder="Ingrese valor"
                    value="{{ old('valor', $nom_practicas_estudios->valor) }}" />
            </div>
            {{-- Moneda agregada --}}
            <div class="col-md-3">
                <label class="small mb-1" for="moneda">Moneda</label>
                <select class="form-select" id="moneda" name="moneda">
                    <option value="ARS"
                        {{ old('moneda', $nom_practicas_estudios->moneda) == 'ARS' ? 'selected' : '' }}>ARS</option>
                    <option value="USD"
                        {{ old('moneda', $nom_practicas_estudios->moneda) == 'USD' ? 'selected' : '' }}>USD</option>
                </select>
            </div>
            <input type="hidden" name="aplica_porcent_adic">
        </div>
    </div>
    <hr />
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>