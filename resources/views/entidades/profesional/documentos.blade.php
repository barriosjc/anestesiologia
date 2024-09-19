@extends('layouts.main')

@section('contenido')
    <section class="content container-fluid">  
        <div class="card card-default">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title">Carga de documentación del profesional, Nro: {{$profesional_id}}</span>
                <div>
                    <a href="{{ route('profesionales.index', $profesional_id) }}" class="btn btn-info btn-sm" data-placement="left">
                        Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <input type="hidden" name="profesional_id" id="profesional_id" value="{{ old('profesional_id', $profesional_id) }}">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tabla_data">
                        <thead class="thead">
                            <tr>
                                <th>Nro hoja</th>
                                <th>Documento</th>
                                <th>Vence</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prof_docum as $item)
                                <tr>
                                    <td>{{ $item->nro_hoja }}</td>
                                    <td>{{ $item->documento->nombre }}</td>
                                    <td>{{ $item->fecha_vctoy }}</td>
                                    <td class="text-end">
                                        <form id="delete-form-{{ $item->id }}"
                                            action="{{ route('profesional.borrar.documentacion', $item->id) }}" method="POST">
                                            <a class="btn btn-sm btn-warning"
                                                href="{{ route('profesional.download.documentacion', $item->id) }}"><i
                                                    class="fa fa-fw fa-download"></i></a>
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm" title="Delete documento"
                                                onclick="confirmDelete({{ $item->id }})"><i
                                                    class="far fa-trash-alt text-white"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($prof_docum) == 0)
                                <tr>
                                    <td colspan="4" class="text-center">No hay cargada documentación hasta el momento.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <form action="{{ route('profesional.guardar.documentacion') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row gx-3 mb-3">
                        <div class="col-md-3">
                            <input type="hidden" name="profesional_id" id="profesional_id"
                                value="{{ old('profesional_id', $profesional_id) }}">

                            <label class="small mb-1">Tipo Documento</label>
                            <select name="documento_id" class="form-select" id="documento_id" required>
                                <option value=""> --- Select ---</option>
                                @foreach ($documentos as $data)
                                        {{-- {{ old('documento_id', $parte->documento_id) == $data->id ? 'selected' : '' }}> --}}
                                        <option value="{{ $data->id }}">
                                        {{ $data->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_vcto">Vencimiento </label>
                            <input type="date" class="form-control" id="fecha_vcto" name="fecha_vcto"
                                data-bs-toggle="tooltip" title="Ingrese la fecha de vencimiento del documento a subir.">
                        </div>
                        <div class="col-md-1">
                            <label for="nro_hoja">Nro. </label>
                            <input type="text" class="form-control" id="nro_hoja" name="nro_hoja" required
                                data-bs-toggle="tooltip" title="Debe ingresar un valor numérico.">
                        </div>
                        <div class="col-md-6">
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
    </section>
    <script src="{{ asset('js/util.js') }}"></script>


@endsection
