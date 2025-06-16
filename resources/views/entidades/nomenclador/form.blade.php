<div class="box box-info padding-1">
    <div class="box-body">
        <input type="hidden" name="id" value="{{$nomenclador->id}}">
        <input type="hidden" name="nom_padre_id" value="{{$nomenclador->nom_padre_id}}">
        <div class="row gx-3 mb-3">
            <div class="col-md-3">
                <label class="small mb-1" for="codigo">Código</label>
                <input class="form-control" id="codigo" name="codigo" type="text" placeholder="codigo"
                    value="{{ old('codigo', $nomenclador->codigo) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1" for="nivel">Nivel</label>
                <input class="form-control" id="nivel" name="nivel" type="text" placeholder="nivel"
                    value="{{ old('nivel', $nomenclador->nivel) }}" />
            </div>
            <div class="col-md-6">
                <label class="small mb-1" for="descripcion">Descripción</label>
                <input class="form-control" id="descripcion" name="descripcion" type="text"
                    placeholder="Ingrese descripción de la práctica" value="{{ old('descripcion', $nomenclador->descripcion) }}" />
            </div>
        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>

