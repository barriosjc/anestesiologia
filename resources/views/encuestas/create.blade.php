@extends('layouts.main')

@section('titulo', 'Alta de encuestas')
@section('contenido')
    <link href="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/datatables.min.css" rel="stylesheet" />

    <!-- Main page content-->
    <div class="container-fluid px-4">
        <div class="row gx-4">
            <div class="col-lg-12">
                <div class="card card-header-actions mb-4">
                    <form method="POST" action="{{ route('encuesta.store') }}" role="form">
                        @csrf
                        <div class="card-header">
                            Encuestas
                            <i class="text-muted" data-feather="info" data-bs-toggle="tooltip" data-bs-placement="left"
                                title="Se cargan los datos de la cabecera de la encuesta o seleccione una encuesta de la grilla para ver Opciones y Periodos ."></i>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="hidden" name="empresas_id" value={{ $empresas_id }}>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <table id="tabla_encuestas" name="tabla_encuestas">
                                                    <thead>
                                                        <tr>
                                                            <th>Empresa</th>
                                                            <th>Nombre</th>
                                                            <th>Edicion</th>
                                                            <th>Op. x Col.</th>
                                                            <th>Habilitado</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($encuestas as $encuesta)
                                                            <tr>
                                                                <td>{{ $encuesta->razon_social }}</td>
                                                                <td>{{ $encuesta->encuesta }}</td>
                                                                <td>{{ $encuesta->edicion }}</td>
                                                                <td>{{ $encuesta->opcionesxcol }}</td>
                                                                <td>{{ $encuesta->habilitada }}</td>
                                                                <td>
                                                                    <button
                                                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2">
                                                                        <i class="fa-regular fa-pen-to-square"></i></button>
                                                                    <button
                                                                        class="btn btn-datatable btn-icon btn-transparent-dark"><i
                                                                            class="fa-regular fa-trash-can"></i></button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <h4>Cargue los datos de la encuesta</h4>
                                <div class="form-group row">
                                    <label for="user_id_reconocido" class="col-sm-2 col-form-label">Empresa</label>
                                    <div class="col-sm-8">
                                        <select name="empresas_id" class="form-control" id="empresas_id">
                                            <option value=""> --- Select ---</option>
                                            @foreach ($empresas as $data)
                                                <option
                                                    value="{{ $data->id }}"{{ old('empresas_id') == $data->id ? 'selected' : '' }}>
                                                    {{ $data->razon_social }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="encuesta" class="col-sm-2 col-form-label">Nombre</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="encuesta" name='encuesta'
                                            placeholder="encuesta" value="{{ old('encuesta') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="edicion" class="col-sm-2 col-form-label">Edición</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="edicion" name="edicion"
                                            placeholder="edicion" value="{{ old('edicion') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="encuestaesxcol" class="col-sm-2 col-form-label">Opciones por columna</label>
                                    <div class="col-sm-2">
                                        <input type="number" min="2" max="5" class="form-control"
                                            id="opcionesxcol" name="opcionesxcol" placeholder="opcionesxcol"
                                            value="{{ old('opcionesxcol') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label"></label>
                                    <div class="col-sm-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="habilitada"
                                                name="habilitada" checked>
                                            <label class="form-check-label" for="habilitado">Habilitado (No/Si)</label>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <div class="card-footer text-muted">
                            <div class="col-12">
                                <div class="box-footer mt20">
                                    <button type="submit" class="btn btn-primary">{{ __('Guardar datos') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row gx-4">
            <div class="col-lg-12">
                <div class="card card-header-actions mb-4">
                    <form method="POST" action="{{ route('encuesta.store') }}" role="form">
                        @csrf
                        <div class="card-header">
                            Periodos
                            <i class="text-muted" data-feather="info" data-bs-toggle="tooltip" data-bs-placement="left"
                                title="Se cargan los datos de la cabecera de la encuesta o seleccione una encuesta de la grilla para ver Opciones y Periodos ."></i>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="hidden" name="encuestas_id" value={{ $encuestas_id }}>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <table id="tabla_periodos" name="tabla_periodos">
                                                    <thead>
                                                        <tr>
                                                            <th>Rango</th>
                                                            <th>Desde</th>
                                                            <th>Hasta</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($periodos as $periodo)
                                                            <tr>
                                                                <td>{{ $periodo->descrip_rango }}</td>
                                                                <td>{{ $periodo->desde }}</td>
                                                                <td>{{ $periodo->hasta }}</td>
                                                                <td>
                                                                    <button
                                                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2">
                                                                        <i
                                                                            class="fa-regular fa-pen-to-square"></i></button>
                                                                    <button
                                                                        class="btn btn-datatable btn-icon btn-transparent-dark"><i
                                                                            class="fa-regular fa-trash-can"></i></button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <h4>Cargue los datos de la encuesta</h4>
                                <div class="form-group row">
                                    <label for="user_id_reconocido" class="col-sm-2 col-form-label">Empresa</label>
                                    <div class="col-sm-8">
                                        <select name="empresas_id" class="form-control" id="empresas_id">
                                            <option value=""> --- Select ---</option>
                                            @foreach ($empresas as $data)
                                                <option
                                                    value="{{ $data->id }}"{{ old('empresas_id') == $data->id ? 'selected' : '' }}>
                                                    {{ $data->razon_social }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="encuesta" class="col-sm-2 col-form-label">Nombre</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="encuesta" name='encuesta'
                                            placeholder="encuesta" value="{{ old('encuesta') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="edicion" class="col-sm-2 col-form-label">Edición</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="edicion" name="edicion"
                                            placeholder="edicion" value="{{ old('edicion') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="encuestaesxcol" class="col-sm-2 col-form-label">Opciones por
                                        columna</label>
                                    <div class="col-sm-2">
                                        <input type="number" min="2" max="5" class="form-control"
                                            id="opcionesxcol" name="opcionesxcol" placeholder="opcionesxcol"
                                            value="{{ old('opcionesxcol') }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label"></label>
                                    <div class="col-sm-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="habilitada" name="habilitada" checked>
                                            <label class="form-check-label" for="habilitado">Habilitado
                                                (No/Si)</label>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <div class="card-footer text-muted">
                            <div class="col-12">
                                <div class="box-footer mt20">
                                    <button type="submit" class="btn btn-primary">{{ __('Guardar datos') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card card-header-actions mb-4">
            <form method="POST" action="{{ route('encuesta.store') }}" role="form">
                @csrf
                <div class="card-header">
                    Opciones
                    <i class="text-muted" data-feather="info" data-bs-toggle="tooltip" data-bs-placement="left"
                        title="Se cargan los datos de cada uno de los 'Motivos' que va a tener la encuesta, todos los cargados se van a ir viendo en la grilla pudiendo quitarlos o editar los datos."></i>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-lg-12">
                                <table id="tabla_opciones" name="tabla_opciones">
                                    <thead>
                                        <tr>
                                            <th>Opción</th>
                                            <th>Puntos</th>
                                            <th>Orden</th>
                                            <th>Style</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($encuestas_opciones as $opcion)
                                            <tr>
                                                <td>{{ $opcion->descripcion }}</td>
                                                <td>{{ $opcion->puntos }}</td>
                                                <td>{{ $opcion->orden }}</td>
                                                <td>{{ $opcion->style }}</td>
                                                <td>
                                                    <button class="btn btn-datatable btn-icon btn-transparent-dark me-2">
                                                        <i class="fa-regular fa-pen-to-square"></i></button>
                                                    <button class="btn btn-datatable btn-icon btn-transparent-dark"><i
                                                            class="fa-regular fa-trash-can"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <h4>Cargue los datos de cada opción</h4>
                        <div class="form-group row">
                            <label for="user_id_reconocido" class="col-sm-2 col-form-label">Opción</label>
                            <div class="col-sm-8">
                                <select name="opicones_id" class="form-control" id="opicones_id">
                                    <option value=""> --- Select ---</option>
                                    @foreach ($opciones as $data)
                                        <option
                                            value="{{ $data->id }}"{{ old('opicones_id') == $data->id ? 'selected' : '' }}>
                                            {{ $data->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="puntos" class="col-sm-2 col-form-label">Puntos</label>
                            <div class="col-sm-2">
                                <input type="number" min="2" max="5" class="form-control" id="puntos"
                                    name="puntos" placeholder="puntos">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="orden" class="col-sm-2 col-form-label">Orden</label>
                            <div class="col-sm-2">
                                <input type="number" min="1" max="{{ count($opciones) }}" class="form-control"
                                    id="orden" name="orden" placeholder="orden">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="style" class="col-sm-2 col-form-label">Style</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" id="style" name="style"
                                    placeholder="Ingrese el Style aplicar a la opción">
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-footer text-muted">
                    <div class="col-12">
                        <div class="box-footer mt20">
                            <button type="submit" class="btn btn-primary">{{ __('Guardar datos') }}</button>
                        </div>
                    </div>
                </div>
        </div>

    </div>

    <script src="https://cdn.datatables.net/v/dt/jq-3.6.0/dt-1.13.4/datatables.min.js"></script>
    {{-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> --}}
    <script>
        document.addEventListener("DOMContentLoaded", function(event) {
            console.log("paso por aca");
            $('#tabla_encuestas').DataTable({
                paging: false,
                ordering: false,
                info: false,
            });

            $('#tabla_periodos').DataTable({
                paging: true,
                ordering: true,
                info: false,
            });

            $('#tabla_opciones').DataTable({
                paging: true,
                ordering: true,
                info: false,
            });

        });
    </script>


@endsection
