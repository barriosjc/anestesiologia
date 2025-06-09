<div class="box box-info padding-1">
    <div class="box-body">
        <input type="hidden" name="id" value="{{$presupuestosCab->id}}">
        <div class="row gx-3 mb-3">
            <div class="col-md-3">
                <label class="small mb-1">Gerenciadora</label><span class='s-red'>*</span>
                <select name="gerenciadora_id" class="form-select" id="gerenciadora_id" required>
                    <option value=""> -- Seleccione --</option>
                    @foreach ($gerenciadoras as $data)
                        <option value="{{ $data->id }}" {{old('gerenciadora_id', $presupuestosCab->gerenciadora_id) == $data->id ? 'selected' : ''}}>    
                            {{ $data->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small mb-1" for="fecha">Fecha</label><span class='s-red'>*</span>
                <input class="form-control" id="fecha" name="fecha" type="date" placeholder="fecha"
                    value="{{ old('fecha', $presupuestosCab->fecha) }}" />
            </div>
            <div class="col-md-3">
                <label class="small mb-1">Centro</label><span class='s-red'>*</span>
                <select name="centro_id" class="form-select" id="centro_id" >
                    <option value=""> -- Seleccione --</option>
                    @foreach ($centros as $data)
                        <option value="{{ $data->id }}" {{old('centro_id', $presupuestosCab->centro_id) == $data->id ? 'selected' : ''}}>    
                            {{ $data->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="small mb-1">Profesional</label>
                <select name="profesional_id" class="form-select" id="profesional_id" >
                    <option value=""> -- Seleccione --</option>
                    @foreach ($profesionales as $data)
                        <option value="{{ $data->id }}" {{old('profesional_id', $presupuestosCab->profesional_id) == $data->id ? 'selected' : ''}}> 
                            {{ $data->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small mb-1" for="valor_dolar">Cotización Uds</label><span class='s-red'>*</span>
                <input class="form-control" id="valor_dolar" name="valor_dolar" type="number"
                    data-bs-toggle="tooltip" step="0.01" min="0" 
                    data-bs-title="Para ingresar un valor correcto separar decimales con punto." value="{{ old('valor_dolar', !empty($presupuestosCab->valor_dolar) ? $presupuestosCab->valor_dolar : $uds ) }}" />
            </div>
            <div class="col-md-6">
                <label class="small mb-1" for="nombre">Nombre del paciente</label><span class='s-red'>*</span>
                <input class="form-control" id="nombre" name="nombre" type="text"
                    placeholder="Ingrese nombre paciente" value="{{ old('nombre', $presupuestosCab->nombre) }}" />
            </div>
            <div class="col-md-2">
                <label class="small mb-1" for="dni">D.N.I.</label>
                <input class="form-control" id="dni" name="dni" type="text"
                    placeholder="Ingrese DNI" value="{{ old('dni', $presupuestosCab->dni) }}" />
            </div>
            <div class="col-md-2">
                <label class="small mb-1" for="fecha_nac">Fecha Nac.</label>
                <input class="form-control" id="fecha_nac" name="fecha_nac" type="date"
                    placeholder="Ingrese fecha nac." value="{{ old('fecha_nac', $presupuestosCab->fecha_nac) }}" />
            </div>
            <div class="col-md-12">
                <label class="small mb-1" for="observaciones">Observaciones</label>
                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                    placeholder="Se recomienda no ingresar prácticas a realizar aquí, solo observaciones." >{{ old('observaciones', $presupuestosCab->observaciones) }}</textarea>
            </div>
        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Guardar e ingresar detalle') }}</button>
    </div>
</div>

