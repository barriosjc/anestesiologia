<div class="box box-info padding-1">
    <div class="box-body">
        <div class="row gx-3 mb-3">
            <div class="col-md-3">
                <label class="small mb-1" for="grupo">Grupo</label>
                <input class="form-control" id="grupo" name="grupo" type="text" placeholder="grupo"
                    value="{{ old('grupo', $nom_practicas_estudios->grupo) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="codigo">Nivel o Código</label>
                <input class="form-control" id="codigo" name="codigo" type="text"
                    placeholder="Ingrese descripción de la práctica" value="{{ old('codigo', $nom_practicas_estudios->codigo) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="tipo">Tipo</label>
                <input class="form-control" id="tipo" name="tipo" type="text"
                    placeholder="Ingrese descripción de la práctica" value="{{ old('tipo', $nom_practicas_estudios->tipo) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="valor">Valor</label>
                <input class="form-control" id="valor" name="valor" type="text"
                    placeholder="Ingrese descripción de la práctica" value="{{ old('valor', $nom_practicas_estudios->valor) }}" />
            </div>
        </div>
    </div>
    <hr />
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>

