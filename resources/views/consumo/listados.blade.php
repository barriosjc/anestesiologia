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
                                {{ __('Generar rendiciones') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="reportForm" action="{{ route('consumo.rendiciones.listar') }}" method='POST' target="_blank">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="cobertura_id">Coberturas</label>
                                    <select class="form-select form-select-sm" id="cobertura_id" name="cobertura_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($coberturas as $item)
                                            <option value="{{ $item->id }}" {{old("cobertura_id") == $item->id ? "selected" : "" }}>{{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="centro_id">Centros</label>
                                    <select class="form-select form-select-sm" id="centro_id" name="centro_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($centros as $item)
                                            <option value="{{ $item->id }}" {{old("centro_id") == $item->id ? "selected" : "" }}>{{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="profesional_id">Profesional</label>
                                    <select class="form-select form-select-sm" id="profesional_id" name="profesional_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($profesionales as $item)
                                            <option value="{{ $item->id }}" {{old("profesional_id") == $item->id ? "selected" : "" }}>{{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="centro_id">Paciente</label>
                                    <input type="text" class="form-control form-control-sm" name="nombre"
                                        value="{{old("nombre")}}" placeholder="Nombre del paciente">
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="form-group col-md-3">
                                    <label class="small mb-1" for="estado_id">Estados</label>
                                    <select class="form-select select2-multiple" id="estados" name="estados[]" multiple>
                                        @foreach ($estados as $item)
                                            <option value="{{ $item->id }}" {{old("estados", 5) == $item->id ? "selected" : "" }}>{{ $item->descripcion }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="small mb-1" for="cobertura_id">Periodo generado</label>
                                    <select class="form-select form-select-sm" id="periodo_gen" name="periodo_gen">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($periodos as $item)
                                            <option value="{{ $item->nombre }}"{{old("periodo_gen") == $item->nombre ? "selected" : "" }}>{{ $item->nombre }} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-1" for="fec_desde">Fec. qx desde</label>
                                    <input class="form-control form-control-sm" id="fec_desde" name="fec_desde"
                                        type="date" placeholder="Ingrese fecha desde" value="{{ old("fec_desde") }}" />
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-1" for="fec_hasta">Fec. qx hasta</label>
                                    <input class="form-control form-control-sm" id="fec_hasta" name="fec_hasta"
                                        type="date" placeholder="Ingrese fecha hasta" value="{{ old("fec_hasta") }}" />
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="form-group col-md-10 ">
                                    <label class="small mb-1" for="cobertura_id">Reportes a generar</label>
                                    <select class="form-select form-select-sm" id="reporte_id" name="reporte_id">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($listados as $item)
                                            <option value="{{ $item->id }}"{{old("reporte_id") == $item->id ? "selected" : "" }}>{{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2 d-flex align-items-end">
                                    <button id="submitInputs" class="btn btn-primary btn-sm" type="submit">Generar listado</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/util.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();

            $('.select2-multiple').select2({
                placeholder: "Select options",
                allowClear: true
            });
        });
    </script>
@endsection
