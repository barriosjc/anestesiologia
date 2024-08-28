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
                        <form id="reportForm" action="{{ route('consumo.rendiciones.filtrar') }}" method='GET'>
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
                                <div class="form-group col-md-2">
                                    <label class="small mb-1" for="periodo_gen">Periodo generado</label>
                                    <select class="form-select form-select-sm" id="periodo_gen" name="periodo_gen">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($periodos as $item)
                                            <option value="{{ $item->sigla }}">
                                                {{ $item->nombre }}
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


                        <form id="generate_rendicion_form" method="POST" action="{{ route('consumo.rendiciones.store') }}">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table_data">
                                    <thead class="thead">
                                        <tr>
                                            <th>
                                                <div class="form-check form-switch">
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- id="ck_{{ $item->consumos_det_id }}" --}}
                                        @foreach ($partes as $item)
                                            <tr>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input ck_item" type="checkbox"
                                                            data-parte-id="{{ $item->parte_cab_id }}" value="{{ $item->consumos_det_id }}">
                                                    </div>
                                                </td>
                                                <td>{{ $item->parte_cab_id }}</td>
                                                <td>{{ $item->fec_prestacion }}</td>
                                                <td>{{ $item->pac_nombre }}</td>
                                                <td>{{ $item->nivel . '/' . $item->codigo . '/' . $item->nom_descripcion }}</td>
                                                <td>{{ $item->porcentaje }}</td>
                                                <td>{{ number_format((float) $item->valor, 2, ',', '.') }}</td>
                                                <td>
                                                    <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                        @if (!empty($item->observacion)) data-bs-title="{{ $item->observacion }}" @endif
                                                        class="badge bg-{{ $item->estado_id == 4 ? 'primary' : 
                                                                            ($item->estado_id == 5 ? 'success' :
                                                                            ($item->estado_id == 6 ? 'warning' :
                                                                            ($item->estado_id == 7 ? 'info' : 
                                                                            'danger'))) }}">{{ $item->est_descripcion }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if (empty($partes))
                                            <tr colspan="9" class="text-center">No hay cargados consumos hasta el momento.
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        
                            <div class="row align-items-end">
                                <div id="periodo_asig" class="form-group col-md-2" >
                                    <label class="small mb-1" for="periodo">Periodo de rendición</label>
                                    <select class="form-select form-select-sm" id="periodo" name="periodo">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($periodos as $item)
                                            <option value="{{ $item->nombre }}">
                                                {{ $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" id="generate_rendicion_btn" class="btn btn-primary btn-sm">
                                        {{ __('Generar rendición') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row align-items-end pt-2">
                                <div class="form-group col-md-2">
                                    <label class="small mb-1" for="estadoCambio">Cabio de estado</label>
                                    <select class="form-select form-select-sm" id="estadoCambio" name="estadoCambio">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($estados as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->descripcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="div_refac" class="form-group col-md-2" style="display: none">
                                    <label class="small mb-1" for="periodo_refac">Periodo a refacturar</label>
                                    <select class="form-select form-select-sm" id="periodo_refac" name="periodo_refac">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($periodos as $item)
                                            <option value="{{ $item->nombre }}">
                                                {{ $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="div_refac2" class="form-group col-md-3" style="display: none">
                                    <label class="small mb-1" for="obs_refac">Observaciones</label>
                                    <textarea class="form-control form-control-sm" id="obs_refac" name="obs_refac"
                                        rows="3" placeholder="Por que pasa a refacturar? "></textarea>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" id="cambio_estados_btn" class="btn btn-info btn-sm">
                                        {{ __('Cambiar estados') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- {!! $partes->links() !!} --}}
            </div>
        </div>
    </div>
    <script src="{{ asset('js/util.js') }}"></script>

    <script>
        $("#estadoCambio").on('change', function(){
            let value = $(this).val();
            // si se selecciona aRefactuar
            if(value === '7'){
                $("#div_refac").css('display', 'block');
                $("#div_refac2").css('display', 'block');
                $("#div_esconde").css('display', 'none');
            } else {
                // Si no, mantener el display none
                $("#div_refac").css('display', 'none');
                $("#div_refac2").css('display', 'none');
                $("#div_esconde").css('display', 'block');
                // $(this).prop('selectedIndex', 0);
            }
        })

        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();

            // marca todos las filas de la tabla
            document.getElementById('ck_todo').addEventListener('change', function() {
                var checkboxes = document.querySelectorAll('.ck_item');
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = document.getElementById('ck_todo').checked;
                });
            });
    
            let token = document.querySelector('input[name="_token"]').value;

            // hacer submit de filas marcadas
            $('#generate_rendicion_btn').on('click', function() {
                let selectedItems = [];
                $('.ck_item:checked').each(function() {
                    let consumo_det_id = $(this).val();
                    let parte_id = $(this).data('parte-id');
                    selectedItems.push({ consumo_det_id: consumo_det_id, parte_id: parte_id });
                });

                let periodo = $('#periodo').val();

                if (selectedItems.length === 0 || !periodo) {
                    alert('Debe seleccionar al menos una fila y un periodo.');
                    return;
                }

                $('<input>').attr({
                    type: 'hidden',
                    name: 'selected_ids',
                    value: JSON.stringify(selectedItems)
                }).appendTo('#generate_rendicion_form');

                $('#generate_rendicion_form').submit();
            });

            // Enviar filas seleccionadas para cambiar el estado
            $('#cambio_estados_btn').on('click', function() {
                let selectedItems = [];
                $('.ck_item:checked').each(function() {
                    let consumo_det_id = $(this).val();
                    let parte_id = $(this).data('parte-id');
                    selectedItems.push({ consumo_det_id: consumo_det_id, parte_id: parte_id });
                });

                let estadoCambio = $('#estadoCambio').val();
                let periodo_refac = $('#periodo_refac').val();
                let obs_refac = $('#obs_refac').val();

                // if (selectedItems.length === 0 || !estadoCambio) {
                //     alert('Debe seleccionar al menos una fila y un estado.');
                //     return;
                // }

                $.ajax({
                    url: '{{ route('consumo.rendiciones.estados') }}',
                    method: 'POST',
                    data: {
                        _token: token,
                        selected_ids: selectedItems,
                        estadoCambio: estadoCambio,
                        periodo_refac: periodo_refac,
                        obs_refac: obs_refac
                    },
                    success: function(response) {
                        const alertDiv = '<div class="alert alert-success py-2">' + response.success + '</div>';
                        $('#alert-container').html(alertDiv);
                    },
                    error: function(response) {
                        let errorMessages = '';
                        if (response.responseJSON && response.responseJSON.errors) {
                            for (const [field, messages] of Object.entries(response.responseJSON.errors)) {
                                errorMessages += "<li>"+messages+"</li>";
                            }
                        } else {
                            errorMessages = 'Ocurrió un error inesperado.';
                        }

                        const alertDiv = '<div class="alert alert-danger py-2"><ul class="no-bullets">' + errorMessages + '</ul></div>';
                        $('#alert-container').html(alertDiv);
                    },
                    complete: function() {
                        const ytop = $('#alert-container').offset().top;
                        window.scrollTo({
                            top: ytop - 300, 
                            behavior: 'smooth'
                        });
                    }
                });

            });
        });
    </script>
@endsection
