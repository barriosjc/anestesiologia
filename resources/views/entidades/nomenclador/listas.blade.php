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
                            <form id="reportForm" action="{{ route('nomenclador.listas.filtrar') }}" method='GET'>
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="gerenciadora_id">Gerenciadoras</label>
                                    <select class="form-select form-select-sm" id="gerenciadora_id" name="gerenciadora_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($gerenciadoras as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('gerenciadora_id') == $item->id ? 'selected' : '' }}>{{ $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
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
                                <div class="form-group col-md-2">
                                    <label class="small mb-1" for="periodo">Periodo</label>
                                    <select class="form-select form-select-sm" id="periodo" name="periodo">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($periodos as $item)
                                            <option
                                                value="{{ $item->nombre }}"{{ old('periodo') == $item->nombre ? 'selected' : '' }}>
                                                {{ $item->nombre }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                    <label class="small mb-1" for="grupo">Grupo</label>
                                    <input type="text" class="form-control form-control-sm" name="grupo"
                                        value="{{ old('grupo') }}">
                                </div>
                                <div class="form-group col-md-1 d-flex align-items-end">
                                    <button id="submitInputs" class="btn btn-primary btn-sm" type="submit">
                                        Buscar</button>
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
                                                        href="{{ route('nomenclador.valores.filtrar', ["grupo" => $item->grupo]) }}"><i
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
