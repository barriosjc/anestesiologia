@extends('layouts.main')

@section('template_title')
    Listas de precios
@endsection

@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Listas de precios') }}
                            </span>

                            <div class="float-right">
                                <a href="{{ route('nomenclador.listas.nuevo') }}" class="btn btn-primary btn-sm float-right"
                                    data-placement="left">
                                    {{ __('Nuevo') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="reportForm" action="{{ route('nomenclador.listas.buscar') }}" method='POST'
                            target="_blank">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="cobertura_id">Coberturas</label>
                                    <select class="form-select form-select-sm" id="cobertura_id" name="cobertura_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($coberturas as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('cobertura_id') == $item->id ? 'selected' : '' }}>{{ $item->sigla }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="centro_id">Centros</label>
                                    <select class="form-select form-select-sm" id="centro_id" name="centro_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($centros as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('centro_id') == $item->id ? 'selected' : '' }}>{{ $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="profesional_id">Profesional</label>
                                    <select class="form-select form-select-sm" id="profesional_id" name="profesional_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($profesionales as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('profesional_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="centro_id">Paciente</label>
                                    <input type="text" class="form-control form-control-sm" name="nombre"
                                        value="{{ old('nombre') }}" placeholder="Nombre del paciente">
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="estado_id">Estados</label>
                                    <select class="form-select select2-multiple" id="estados" name="estados[]" multiple>
                                        @foreach ($estados as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('estados') == $item->id ? 'selected' : '' }}>
                                                {{ $item->descripcion }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="small mb-1" for="periodo_gen">Periodo generado</label>
                                    <select class="form-select form-select-sm" id="periodo_gen" name="periodo_gen">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($periodos as $item)
                                            <option
                                                value="{{ $item->nombre }}"{{ old('periodo_gen') == $item->nombre ? 'selected' : '' }}>
                                                {{ $item->nombre }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-1" for="fec_desde">Fec. qx desde</label>
                                    <input class="form-control form-control-sm" id="fec_desde" name="fec_desde"
                                        type="date" placeholder="Ingrese fecha desde" value="{{ old('fec_desde') }}" />
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-1" for="fec_hasta">Fec. qx hasta</label>
                                    <input class="form-control form-control-sm" id="fec_hasta" name="fec_hasta"
                                        type="date" placeholder="Ingrese fecha hasta" value="{{ old('fec_hasta') }}" />
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="user_id">Usuarios</label>
                                    <select class="form-select form-select-sm" id="user_id" name="user_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($users as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('user_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-1" for="fec_desde_adm">Fec. carga desde</label>
                                    <input class="form-control form-control-sm" id="fec_desde_adm" name="fec_desde_adm"
                                        type="date" placeholder="Ingrese fecha desde"
                                        value="{{ old('fec_desde_adm') }}" />
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-1" for="fec_hasta_adm">Fec. carga hasta</label>
                                    <input class="form-control form-control-sm" id="fec_hasta_adm" name="fec_hasta_adm"
                                        type="date" placeholder="Ingrese fecha hasta"
                                        value="{{ old('fec_hasta_adm') }}" />
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="form-group col-md-10 ">
                                    <label class="small mb-1" for="reporte_id">Reportes a generar</label>
                                    <select class="form-select form-select-sm" id="reporte_id" name="reporte_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($listados as $item)
                                            <option
                                                value="{{ $item->id }}"{{ old('reporte_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2 d-flex align-items-end">
                                    <button id="submitInputs" class="btn btn-primary btn-sm" type="submit">Generar
                                        listado</button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>Gerenciadora</th>
                                        <th>Cobertura</th>
                                        <th>Centro</th>
                                        <th>Periodo</th>
                                        <th>Grupo</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($listas as $item)
                                        <tr>
                                            <td>{{ $item->gerenciadora->nombre }}</td>
                                            <td>{{ $item->cobertura->sigla }}</td>
                                            <td>{{ $item->centro->nombre }}</td>
                                            <td>{{ $item->periodo }}</td>
                                            <td>{{ $item->grupo }}</td>
                                            <td>
                                                <form id="delete-form-{{ $item->id }}"
                                                    action="{{ route('nomenclador.listas.borrar', $item->id) }}"
                                                    method="POST">
                                                    <a class="btn btn-sm btn-primary "
                                                        href="{{ route('nomenclador.valores.filtrar', $item->id) }}"><i
                                                            class="fa fa-fw fa-eye"></i></a>
                                                    <a class="btn btn-sm btn-success"
                                                        href="{{ route('nomenclador.listas.modificar', $item->id) }}"><i
                                                            class="fa fa-fw fa-edit"></i></a>
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Borrar lista de precio"
                                                        onclick="confirmDelete({{ $item->id }})"><i
                                                            class="far fa-trash-alt text-white"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if (!empty($listas))
                    {!! $listas->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif
            </div>
        </div>
    </div>
@endsection
