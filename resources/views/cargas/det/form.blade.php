@extends('layouts.main')

@section('contenido')
    <section class="content container-fluid">
        <div class="card card-default">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title">Carga de detalle del parte, Nro: {{ $parte_cab_id }}</span>
                <div>
                    <a class="btn btn-warning btn-sm llama_modal" data-bs-toggle="modal" 
                        data-bs-target="#valorModal" 
                        data-id="{{ $parte_cab_id}}"
                        data-observaciones = "{{ $observaciones}}"
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
                <input type="hidden" name="parte_cab_id" id="parte_cab_id" value="{{ old('parte_cab_id', $parte_cab_id) }}">
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
                                        <form id="delete-form-{{ $item->id }}"
                                            action="{{ route('partes_det.destroy', $item->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm" title="Borrar documento"
                                                onclick="confirmDelete({{ $item->id }})">
                                                <i class="far fa-trash-alt text-white"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if (empty($partes))
                                <tr>
                                    <td colspan="3" class="text-center">No hay cargada documentación hasta el momento.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <form action="{{ route('partes_det.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row gx-3 mb-3">
                        <div class="col-md-3">
                            <input type="hidden" name="parte_cab_id" id="parte_cab_id"
                                value="{{ old('parte_cab_id', $parte_cab_id) }}">

                            <label class="small mb-1">Tipo Documento</label>
                            <select name="documento_id" class="form-select" id="documento_id" required>
                                <option value=""> -- Seleccione --</option>
                                @foreach ($documentos as $data)
                                    <option value="{{ $data->id }}"
                                        {{ old('documento_id', $parte->documento_id) == $data->id ? 'selected' : '' }}>
                                        {{ $data->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label for="archivo">Nro. </label>
                            <input type="text" class="form-control" id="nro_hoja" name="nro_hoja" required
                                data-bs-toggle="tooltip" title="Debe ingresar un valor numérico.">
                        </div>
                        <div class="col-md-8">
                            <label for="archivo">Seleccionar archivo:</label>
                            <input type="file" class="form-control" id="archivo" name="archivo">
                        </div>
                    </div>
                    <div class="box-footer mt20">
                        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @include('cargas.cab.partials.cambio_estado')

    </section>
    <script src="{{ asset('js/util.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Manejar la apertura del modal
            $(document).on('click', '.llama_modal', function() {
                var parteCabId = $(this).data('id');
                var observaciones = $(this).data("observaciones");
                document.querySelector('input[type="hidden"][name="id"]').value = parteCabId;
                document.querySelector('textarea[name="observaciones"]').value = observaciones;
            });
        });
    </script>
    @endsection
