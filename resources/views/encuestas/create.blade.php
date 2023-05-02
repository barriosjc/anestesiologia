@extends('layouts.main')

@section('titulo', 'Alta de encuestas')
@section('contenido')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />

    <!-- Main page content-->
    <div class="container-fluid px-4">
        <form method="POST" action="{{ route('encuesta.store') }}" role="form">
            <div class="row gx-4">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            Encuesta
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label for="user_id_reconocido" class="col-sm-2 col-form-label">Empresa</label>
                                <div class="col-sm-8">
                                    <select name="empresas" class="form-control" id="empresas">
                                        <option value=""> --- Select ---</option>
                                        @foreach ($empresas as $data)
                                            <option
                                                value="{{ $data->id }}"{{ old('empresas') == $data->id ? 'selected' : '' }}>
                                                {{ $data->razon_social }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="encuesta" class="col-sm-2 col-form-label">Nombre</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" id="encuesta" name='encuesta'
                                        placeholder="encuesta">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="edicion" class="col-sm-2 col-form-label">Edición</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="edicion" name="edicion"
                                        placeholder="edicion">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="periodos_id" class="col-sm-2 col-form-label">Periodo</label>
                                <div class="col-sm-8">
                                    <select name="periodos_id" class="form-control" id="periodos_id">
                                        <option value=""> --- Select ---</option>
                                        @foreach ($periodos as $data)
                                            <option
                                                value="{{ $data->id }}"{{ old('periodos_id') == $data->id ? 'selected' : '' }}>
                                                {{ $data->descrip_rango }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="opcionesxcol" class="col-sm-2 col-form-label">Opciones por columna</label>
                                <div class="col-sm-2">
                                    <input type="number" min="2" max="5" class="form-control"
                                        id="opcionesxcol" name="opcionesxcol" placeholder="opcionesxcol">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="" class="col-sm-2 col-form-label"></label>
                                <div class="col-sm-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="habilitado"
                                            name="habilitado">
                                        <label class="form-check-label" for="habilitado">Habilitado (No/Si)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-header-actions mb-4">
                <div class="card-header">
                    Opciones
                    <i class="text-muted" data-feather="info" data-bs-toggle="tooltip" data-bs-placement="left"
                        title="Se cargan los datos de cada uno de los 'Motivos' que va a tener la encuesta, todos los cargados se van a ir viendo en la grilla pudiendo quitarlos o editar los datos."></i>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-lg-12">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>Opción</th>
                                            <th>Puntos</th>
                                            <th>Orden</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>Opción</th>
                                            <th>Puntos</th>
                                            <th>Orden</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <tr>
                                            <td>Cabeza</td>
                                            <td>10</td>
                                            <td>1</td>
                                            <td>
                                                <div class="badge bg-primary text-white rounded-pill">Full-time</div>
                                            </td>
                                            <td>
                                                <button class="btn btn-datatable btn-icon btn-transparent-dark me-2">
                                                    <i class="fa-regular fa-pen-to-square"></i></button>
                                                <button class="btn btn-datatable btn-icon btn-transparent-dark"><i
                                                        class="fa-regular fa-trash-can"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Corazón</td>
                                            <td>8</td>
                                            <td>2</td>
                                            <td>
                                                <div class="badge bg-warning rounded-pill">Pending</div>
                                            </td>
                                            <td>
                                                <button class="btn btn-datatable btn-icon btn-transparent-dark me-2">
                                                    <i class="fa-regular fa-pen-to-square"></i></button>
                                                <button class="btn btn-datatable btn-icon btn-transparent-dark"><i
                                                        class="fa-regular fa-trash-can"></i></button>
                                            </td>
                                        </tr>
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
                        <div class="col-12">
                            <div class="box-footer mt20">
                                <button type="submit" class="btn btn-primary">{{ __('Cargar opción') }}</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="card-footer text-muted">
                    <div class="col-12">
                        <div class="box-footer mt20">
                            <button type="submit" class="btn btn-primary">{{ __('Guardar datos de la encuesta') }}</button>
                        </div>
                    </div>
                  </div>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <script src="{{ asset('libs/sbadmin/js/datatables/datatables-simple-demo.js') }}"></script>
    <script>
        const dataTable = new simpleDatatables.DataTable("#datatablesSimple", {
            searchable: false,
            perPage: 5,
            perPageSelect: false,
        })
    </script>
@endsection
