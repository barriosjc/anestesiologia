<div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title">Carga de documentación del profesional, Nro: {{ $profesionalId }}</span>
                <a href="{{ route('profesionales.index') }}" class="btn btn-info btn-sm" data-placement="left">
                    Volver
                </a>
            </div>
            <div class="card-body">
                @include('utiles.alerts')

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead">
                            <tr>
                                <th>Nro hoja</th>
                                <th>Documento</th>
                                <th>Vence</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($prof_docum as $item)
                                <tr wire:key="prof-doc-{{ $item->id }}">
                                    <td>{{ $item->nro_hoja }}</td>
                                    <td>{{ $item->documento->nombre }}</td>
                                    <td>{{ $item->fecha_vctoy }}</td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-warning"
                                            href="{{ route('profesional.download.documentacion', $item->id) }}"><i
                                                class="fa fa-fw fa-download"></i></a>
                                        <button type="button" class="btn btn-danger btn-sm" title="Delete documento"
                                            onclick="confirmDelete({{ $item->id }}, 'Confirma eliminar la documentación?', () => @this.borrar({{ $item->id }}))">
                                            <i class="far fa-trash-alt text-white"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay cargada documentación hasta el momento.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <form wire:submit="guardar" enctype="multipart/form-data">
                    <div class="row gx-3 mb-3">
                        <div class="col-md-3">
                            <label class="small mb-1">Tipo Documento</label>
                            <select wire:model="documento_id" class="form-select" required>
                                <option value=""> -- Seleccione --</option>
                                @foreach ($documentos as $data)
                                    <option value="{{ $data->id }}">{{ $data->nombre }}</option>
                                @endforeach
                            </select>
                            @error('documento_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_vcto">Vencimiento</label>
                            <input type="date" class="form-control" wire:model="fecha_vcto" id="fecha_vcto"
                                data-bs-toggle="tooltip" title="Ingrese la fecha de vencimiento del documento a subir.">
                            @error('fecha_vcto') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-1">
                            <label for="nro_hoja">Nro.</label>
                            <input type="text" class="form-control" wire:model="nro_hoja" id="nro_hoja" required
                                data-bs-toggle="tooltip" title="Debe ingresar un valor numérico.">
                            @error('nro_hoja') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="archivo">Seleccionar archivo:</label>
                            <input type="file" class="form-control" wire:model="archivo" id="archivo">
                            @error('archivo') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="box-footer mt20">
                        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
