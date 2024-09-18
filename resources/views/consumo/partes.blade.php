@extends('layouts.main')

@section('contenido')
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.css" /> --}}
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="card_title">
                                {{ __('Auditoria Carga') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="reportForm" action="{{ route('consumos.partes.filtrar') }}" method='GET'>
                            {{-- @csrf --}}
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="cobertura_id">Coberturas</label>
                                    <select class="form-select form-select-sm" id="cobertura_id" name="cobertura_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($coberturas as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $cobertura_id == $item->id ? 'selected' : '' }}>{{ $item->sigla }}
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
                                                {{ $centro_id == $item->id ? 'selected' : '' }}>{{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="profesional_id">Profesional</label>
                                    <select class="form-select form-select-sm" id="profesional_id" name="profesional_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($profesionales as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $profesional_id == $item->id ? 'selected' : '' }}>{{ $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="centro_id">Paciente</label>
                                    <input type="text" class="form-control form-control-sm" name="nombre"
                                        value="{{ $nombre }}" placeholder="Nombre del paciente">
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="col-md-2">
                                    <label class="small mb-1" for="fec_desde">Fec. qx desde</label>
                                    <input class="form-control form-control-sm" id="fec_desde" name="fec_desde"
                                        type="date" placeholder="Ingrese fecha desde" value="{{ $fec_desde }}" />
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-1" for="fec_hasta">Fec. qx hasta</label>
                                    <input class="form-control form-control-sm" id="fec_hasta" name="fec_hasta"
                                        type="date" placeholder="Ingrese fecha hasta" value="{{ $fec_hasta }}" />
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="small mb-1" for="estado_id">Estados</label>
                                    <select class="form-select form-select-sm" id="estado_id" name="estado_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($estados as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $estado_id == $item->id ? 'selected' : '' }}>{{ $item->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 d-flex align-items-end">
                                    <button id="submitInputs" class="btn btn-primary btn-sm" type="submit">Filtrar
                                        partes</button>
                                </div>
                            </div>
                        </form>


                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="table_data">
                                <thead class="thead">
                                    <tr>
                                        <th>Nro</th>
                                        <th>Centro</th>
                                        <th>Profesional</th>
                                        <th>Paciente</th>
                                        <th>Fecha</th>
                                        <th>Cobertura</th>
                                        <th>Estado</th>
                                        <th>Docs</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($partes as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->centro }}</td>
                                            <td>{{ $item->profesional }}</td>
                                            <td>{{ $item->paciente }}</td>
                                            <td>{{ $item->fec_prestacion }}</td>
                                            <td>{{ $item->sigla }}</td>
                                            <td>
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" 
                                                    @if(!empty($item->observacion))
                                                        data-bs-title="{{$item->observacion}}"
                                                    @endif
                                                    class="badge bg-{{ $item->est_id == 1 ? 'primary' : 
                                                                        ($item->est_id == 2 ? 'danger' : 
                                                                        ($item->est_id == 3 ? 'warning' :
                                                                        ($item->est_id == 4 ? 'info' :
                                                                        ($item->est_id == 5 ? 'secondary' :
                                                                        'success')))) }}">{{ $item->est_descripcion }}
                                                    @if(!empty($item->observacion))
                                                        <span class="badge text-bg-dark"> </span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td>{{ $item->cantidad }}</td>
                                            <td class="td-actions">
                                                    <a class="btn btn-sm btn-success"
                                                        href="{{ route('consumos.cargar', $item->id) }}"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" 
                                                        data-bs-title="Consultar documentos cargados e ingresar consumo a facturar">
                                                        <i class="fa fa-fw fa-edit"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if(empty($partes))
                                    <tr colspan="9" class="text-center">No hay cargados consumos hasta el momento.</tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if(!empty($partes))
                    {!! $partes->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif
            </div>
        </div>
    </div>
    <script src="{{ asset('js/util.js') }}"></script>

    {{-- <script>
        $(document).ready(function(){
            $('[data-bs-toggle="tooltip"]').tooltip(); 
        });
    </script> --}}
    
@endsection
