{{-- @dd($pagos, $pagos->isEmpty()) --}}
<form id="form_presupuesto" method="POST" action="{{ route('presupuestos.pagos.store', $presupuestosCab->id ) }}"
    role="form" enctype="multipart/form-data">
    @csrf
    <div class="card mt-3 p-3 border">
        <div class="card-body">
            <input type="hidden" name="presupuesto_cab_id" value="{{ $presupuestosCab->id}}">
            <div class="row gx-3 mb-3">
                <div class="col-md-3">
                    <label class="small mb-1">Usuario</label>
                    <input type="hidden" name="usuario_id" value={{ Auth::user()->id }} />
                    <input type="text" class="form-control" disabled value={{ Auth::user()->name }}>
                </div>
                <div class="col-md-3">
                    <label class="small mb-1" for="archivo">Fecha </label>
                    <input type="date" class="form-control" id="fecha" name="fecha" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="small mb-1" for="archivo">Valor ($)</label>
                    <input type="text" class="form-control" id="valor" name="valor" value="0,00"/>
                </div>
            </div>
            <div class="row gx-3 mb-3">
                <div class="col-md-12">
                    <label class="small mb-1" for="observaciones">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                        placeholder=""></textarea>
                </div>
            </div>
            <div class="box-footer mt20">
                <button type="submit" id="submitButton" class="btn btn-primary">{{ __('Guardar') }}</button>
            </div>
        </div>
    </div>
</form>

