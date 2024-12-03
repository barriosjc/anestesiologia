    {{-- modales --}}
    <div class="modal fade" id="valorModal" tabindex="-1" aria-labelledby="valorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="valorModalLabel">Pasar el parte a: A liquidar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('consumos.aprocesar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        {{-- <div class="form-group col-md-3"> --}}
                            <label class="label-control" for="estado_id">Estados</label>
                            <select class="form-select form-select-sm" id="estado_cambio" name="estado_cambio">
                                <option value="">-- Seleccione --</option>
                                @foreach ($estados as $item)
                                    @if($item->id == 3 || $item->id == 9)
                                        <option value="{{ $item->id }}"> {{ $item->descripcion }} </option>
                                    @endif
                                @endforeach
                            </select>
                        {{-- </div> --}}
                        <label class="label-control">Observaciones</label>
                        <textarea rows="4" name="observaciones" class="form-control">  </textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>