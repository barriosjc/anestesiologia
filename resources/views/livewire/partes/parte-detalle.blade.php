<section class="content container-fluid">
    <div class="card card-default">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="card-title">Carga de detalle del parte, Nro: {{ $parte_cab_id }}</span>
            <div>
                <a class="btn btn-warning btn-sm"
                    wire:click="abrirEstadoModal({{ $parte_cab_id }}, @js($observaciones))"
                    data-bs-toggle="tooltip" data-bs-placement="top"
                    data-bs-title="Pasar el estado del parte a A liquidar o Con faltantes">
                    Cambiar estado
                </a>
                <a href="{{ route('partes_cab.edit', $parte_cab_id) }}" class="btn btn-info btn-sm" data-placement="left">
                    Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger py-2">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tabla_data">
                    <thead class="thead">
                        <tr>
                            <th>Nro hoja</th>
                            <th>Documento</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($partes as $item)
                            <tr>
                                <td>{{ $item->nro_hoja }}</td>
                                <td>{{ $item->documento->nombre }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-warning"
                                        href="{{ route('partes_det.download', $item->id) }}"><i
                                            class="fa fa-fw fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" title="Borrar documento"
                                        onclick="confirmDelete({{ $item->id }}, 'Esta acción es irreversible.', () => @this.destroy({{ $item->id }}))">
                                        <i class="far fa-trash-alt text-white"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        @if ($partes->isEmpty())
                            <tr>
                                <td colspan="3" class="text-center">No hay cargada documentación hasta el momento.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            {{ $partes->links() }}

            <form wire:submit="store" enctype="multipart/form-data" novalidate>
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
                    <div class="col-md-1">
                        <label class="small mb-1" for="nro_hoja">Nro. </label>
                        <input type="text" wire:model="nro_hoja" class="form-control" id="nro_hoja"
                            data-bs-toggle="tooltip" title="Debe ingresar un valor numérico.">
                        @error('nro_hoja') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="small mb-1" for="archivo">Seleccionar archivo:</label>
                        <input type="file" wire:model="archivo" class="form-control" id="archivo">
                        @error('archivo') <span class="text-danger small">{{ $message }}</span> @enderror
                        <div wire:loading wire:target="archivo" class="small text-muted mt-1">Subiendo archivo...</div>
                    </div>
                </div>
                <div class="box-footer mt20">
                    <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
                </div>
            </form>
        </div>
    </div>
    <div>
        @include('cargas.cab.partials.cambio_estado')
    </div>
</section>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-modal', (event) => {
                new bootstrap.Modal(document.getElementById(event.modal)).show();
            });
            Livewire.on('close-modal', (event) => {
                bootstrap.Modal.getInstance(document.getElementById(event.modal))?.hide();
            });
        });
    </script>
@endpush
