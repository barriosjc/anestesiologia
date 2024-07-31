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
                                {{ __('Partes') }}
                            </span>
                            <div class="float-right">
                                <a href="{{ route('partes_cab.create') }}" class="btn btn-primary btn-sm float-right"
                                    data-placement="left">
                                    {{ __('Nuevo') }}
                                </a>
                            </div>
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
                                                {{ $cobertura_id == $item->id ? 'selected' : '' }}>{{ $item->nombre }}
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
                                        <th><div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="ck_todo">
                                          </div>
                                        </th>
                                        <th>Nro</th>
                                        <th>F.proc</th>
                                        <th>Paciente</th>
                                        <th>Práctica</th>
                                        <th>%</th>
                                        <th>Valor($)</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($partes as $item)
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input ck_item" type="checkbox" id="ck_{{$item->consumos_det_id}}">
                                                </div>
                                            </td>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->fec_prestacion }}</td>
                                            <td>{{ $item->dni ."/".$item->pac_nombre }}</td>
                                            <td>{{ $item->nivel."/".$item->codigo."/".$item->nom_descripcion }}</td>
                                            <td>{{ $item->porcentaje }}</td>
                                            <td>{{ number_format((float) $item->valor, 2, '.', '') }}</td>
                                            <td>
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" 
                                                    @if(!empty($item->observacion))
                                                        data-bs-title="{{$item->observacion}}"
                                                    @endif
                                                    class="badge bg-{{ $item->estado_id == 1 ? 'primary' : ($item->estado_id == 2 ? 'danger' : 'success') }}">{{ $item->est_descripcion }}</span>
                                            </td>
                                            <td class="td-actions">
                                                    <a class="btn btn-sm btn-success"
                                                        href=""
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
                {{-- {!! $partes->links() !!} --}}
            </div>
        </div>
    </div>
    <script src="{{ asset('js/util.js') }}"></script>

    <script>
        $(document).ready(function(){
            $('[data-bs-toggle="tooltip"]').tooltip(); 
        });

        // marca todos las filas
        document.getElementById('ck_todo').addEventListener('change', function() {
            console.log("hizo click");
        var checkboxes = document.querySelectorAll('.ck_item');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = document.getElementById('ck_todo').checked;
        });
    });
    </script>
    
@endsection
