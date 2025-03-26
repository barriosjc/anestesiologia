<div class="box box-info padding-1">
    <div class="box-body">
        <input type="hidden" name="id" value="{{$nom_practicas_estudios->id}}">
        <div class="row gx-3 mb-3">
            <div class="col-md-3">
                <label class="small mb-1" for="codigo">Código</label>
                <input class="form-control" id="codigo" name="codigo" type="text" placeholder="codigo"
                    value="{{ old('codigo', $nom_practicas_estudios->codigo) }}" />
            </div>
            <div class="col-md-6">
                <label class="small mb-1" for="nombre">Nombre</label>
                <input class="form-control" id="nombre" name="nombre" type="text"
                    placeholder="Ingrese descripción de la práctica" value="{{ old('nombre', $nom_practicas_estudios->nombre) }}" />
            </div>
        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>

