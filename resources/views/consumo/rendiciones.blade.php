@extends('layouts.main')

@section('contenido')
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
                        @include('consumo.partials.rendicion.filtros')

                        <form id="generate_rendicion_form" method="POST"
                            action="{{ route('consumo.rendiciones.store') }}">
                            @csrf
                            <div id="contenedor-grilla" class="table-responsive">
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
                                            <th class="columna-extra">Periodo</th>
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
                                                            data-parte-id="{{ $item->parte_cab_id }}"
                                                            value="{{ $item->consumos_det_id }}">
                                                    </div>
                                                </td>
                                                <td>{{ $item->parte_cab_id }}</td>
                                                <td>{{ $item->fec_prestacion }}</td>
                                                <td>{{ $item->pac_nombre }}</td>
                                                <td class="columna-extra">{{ $item->periodo }}
                                                <td>{{ $item->nivel . '/' . $item->codigo . '/' . $item->nom_descripcion }}
                                                </td>
                                                <td>{{ $item->porcentaje }}</td>
                                                <td>{{ number_format((float) $item->valor, 2, ',', '.') }}</td>
                                                <td>
                                                    <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                        {{-- @if (!empty($item->obs_refac)) data-bs-title="{{ $item->obsrefac }}" @endif --}}
                                                        @if (!empty($item->obs_refac)) data-bs-title="{{ $item->periodo . ' / ' . $item->obs_refac }}" @endif
                                                        class="badge bg-{{ $item->estado_id == 4
                                                            ? 'primary'
                                                            : ($item->estado_id == 5
                                                                ? 'success'
                                                                : ($item->estado_id == 6
                                                                    ? 'warning'
                                                                    : ($item->estado_id == 7
                                                                        ? 'info'
                                                                    : ($item->estado_id == 8
                                                                        ? 'secondary'
                                                                        : 'danger')))) }}">{{ $item->est_descripcion }}
                                                        @if (!empty($item->obs_refac))
                                                            <span class="badge text-bg-dark"> </span>
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if (empty($partes))
                                            <tr colspan="9" class="text-center">No hay cargados consumos hasta el
                                                momento.
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            @if (!empty($partes))
                                {!! $partes->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                            @endif

                            @include('consumo.partials.rendicion.tareas')
                    </div>
                </div>
                {{-- {!! $partes->links() !!} --}}
            </div>
        </div>
        <div id="error-container"></div>
    </div>
    <script src="{{ asset('js/util.js') }}"></script>
    <script>
        // Define un objeto global con todas las rutas
        window.routes = {
            revalorizar: "{{ route('consumo.rendiciones.revalorizar') }}",
            cambiarEstado: "{{ route('consumo.rendiciones.estados') }}",
            agregarConsumo: "{{ route('consumo.rendiciones.agregar') }}",
            agregarConsumoConDiferencia: "{{ route('consumo.rendiciones.agregarydiff') }}",
        };
    </script>
    {{-- <script src="{{ asset('js/rendiciones.js') }}"></script> --}}
    <script src="{{ asset('js/rendiciones.js') }}"></script>
{{-- 
    <script>
        let token = document.querySelector('input[name="_token"]').value;

        $("#estadoCambio").on('change', function() {
            let value = $(this).val();
            // si se selecciona aRefactuar
            if (value === '7') {
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

        function revalorizarPartesJS() {
            let selectedItems = [];
            $('.ck_item:checked').each(function() {
                let consumo_det_id = $(this).val();
                let parte_id = $(this).data('parte-id');
                selectedItems.push({
                    consumo_det_id: consumo_det_id,
                    parte_id: parte_id
                });
            });
            let periodo = $('#periodo_revalorizar').val();

            if (selectedItems.length === 0 || !periodo) {
                alert('Debe seleccionar al menos una fila y un periodo.');
                return;
            }
            $.post("{{ route('consumo.rendiciones.revalorizar') }}", {
                _token: token,
                selected_ids: selectedItems,
                periodo_revalorizar: periodo
            }, function(response) {
                const alertDiv = '<div class="alert alert-success py-2">' + response.success + '</div>';
                $('#alert-container').html(alertDiv);
            }).fail(function(response) {
                let errorMessages = 'Ocurrió un error inesperado.';
                if (response.responseJSON && response.responseJSON.errors) {
                    errorMessages = '';
                    for (const [field, messages] of Object.entries(response.responseJSON.errors)) {
                        errorMessages += "<li>" + messages + "</li>";
                    }
                }
                const alertDiv = '<div class="alert alert-danger py-2"><ul class="no-bullets">' + errorMessages +
                    '</ul></div>';
                $('#alert-container').html(alertDiv);
            }).always(function() {
                const ytop = $('#alert-container').offset().top;
                window.scrollTo({
                    top: ytop - 300,
                    behavior: 'smooth'
                });
            })
        };

        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();

            $('.select2').select2({
                placeholder: "-- Seleccione --",
                allowClear: true
            });

            // marca todos las filas de la tabla
            document.getElementById('ck_todo').addEventListener('change', function() {
                var checkboxes = document.querySelectorAll('.ck_item');
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = document.getElementById('ck_todo').checked;
                });
            });

            // hacer submit de filas marcadas
            $('#generate_rendicion_btn').on('click', function() {
                let selectedItems = [];
                $('.ck_item:checked').each(function() {
                    let consumo_det_id = $(this).val();
                    let parte_id = $(this).data('parte-id');
                    selectedItems.push({
                        consumo_det_id: consumo_det_id,
                        parte_id: parte_id
                    });
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
                    selectedItems.push({
                        consumo_det_id: consumo_det_id,
                        parte_id: parte_id
                    });
                });

                let estadoCambio = $('#estadoCambio').val();
                let periodo_refac = $('#periodo_refac').val();
                let obs_refac = $('#obs_refac').val();

                $.ajax({
                    url: "{{ route('consumo.rendiciones.estados') }}",
                    method: 'POST',
                    data: {
                        _token: token,
                        selected_ids: selectedItems,
                        estadoCambio: estadoCambio,
                        periodo_refac: periodo_refac,
                        obs_refac: obs_refac
                    },
                    success: function(response) {
                        const alertDiv = '<div class="alert alert-success py-2">' + response
                            .success + '</div>';
                        $('#alert-container').html(alertDiv);
                    },
                    error: function(response) {
                        let errorMessages = '';
                        console.log(response.responseJSON.error)
                        if (response.responseJSON && response.responseJSON.errors) {
                            for (const [field, messages] of Object.entries(response.responseJSON
                                    .errors)) {
                                errorMessages += "<li>" + messages + "</li>";
                            }
                        } else if (response.responseJSON.error) {
                            errorMessages += "<li>" + response.responseJSON.error + "</li>";
                        } else {
                            errorMessages = 'Ocurrió un error inesperado.';
                        }

                        const alertDiv =
                            '<div class="alert alert-danger py-2"><ul class="no-bullets">' +
                            errorMessages + '</ul></div>';
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

            // agregar nuevo consumo
            $('#btnAgregar').on('click', function() {
                let selectedItems = [];
                $('.ck_item:checked').each(function() {
                    let consumo_det_id = $(this).val();
                    let parte_id = $(this).data('parte-id');
                    selectedItems.push({
                        consumo_det_id: consumo_det_id,
                        parte_id: parte_id
                    });
                });

                let estadoAgregar = $('#estadoAgregar').val();
                let periodoAgregar = $('#periodoAgregar').val();
                let obsAgregar = $('#obsAgregar').val();
                let valorAgregar = $('#valorAgregar').val();

                $.ajax({
                    url: "{{ route('consumo.rendiciones.agregar') }}",
                    method: 'POST',
                    data: {
                        _token: token,
                        selected_ids: selectedItems,
                        estadoAgregar: estadoAgregar,
                        periodoAgregar: periodoAgregar,
                        obsAgregar: obsAgregar,
                        valorAgregar: valorAgregar
                    },
                    success: function(response) {
                        const alertDiv = '<div class="alert alert-success py-2">' + response
                            .success + '</div>';
                        $('#alert-container').html(alertDiv);
                    },
                    error: function(response) {
                        let errorMessages = '';
                        console.log(response.responseJSON.error)
                        if (response.responseJSON && response.responseJSON.errors) {
                            for (const [field, messages] of Object.entries(response.responseJSON
                                    .errors)) {
                                errorMessages += "<li>" + messages + "</li>";
                            }
                        } else if (response.responseJSON.error) {
                            errorMessages += "<li>" + response.responseJSON.error + "</li>";
                        } else {
                            errorMessages = 'Ocurrió un error inesperado.';
                        }

                        const alertDiv =
                            '<div class="alert alert-danger py-2"><ul class="no-bullets">' +
                            errorMessages + '</ul></div>';
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

            // agregar nuevo consumo y diferencia
            $('#btnAgregaryDiff').on('click', function() {
                let selectedItems = [];
                $('.ck_item:checked').each(function() {
                    let consumo_det_id = $(this).val();
                    let parte_id = $(this).data('parte-id');
                    selectedItems.push({
                        consumo_det_id: consumo_det_id,
                        parte_id: parte_id
                    });
                });
                let estadoAgregar = $('#estadoAgregaryDiff').val();
                let periodoAgregar = $('#periodoAgregaryDiff').val();
                let obsAgregar = $('#obsAgregaryDiff').val();
                let valorAgregar = $('#valorAgregaryDiff').val();
                let refacturar = $('input[name="refacturaryDiff"]:checked').val();

                $.ajax({
                    url: "{{ route('consumo.rendiciones.agregarydiff') }}",
                    method: 'POST',
                    data: {
                        _token: token,
                        selected_ids: selectedItems,
                        estadoAgregar: estadoAgregar,
                        periodoAgregar: periodoAgregar,
                        obsAgregar: obsAgregar,
                        valorAgregar: valorAgregar,
                        refacturar: refacturar
                    },
                    success: function(response) {
                        const alertDiv = '<div class="alert alert-success py-2">' + response
                            .success + '</div>';
                        $('#alert-container').html(alertDiv);
                    },
                    error: function(response) {
                        let errorMessages = '';
                        console.log(response.responseJSON.error)
                        if (response.responseJSON && response.responseJSON.errors) {
                            for (const [field, messages] of Object.entries(response.responseJSON
                                    .errors)) {
                                errorMessages += "<li>" + messages + "</li>";
                            }
                        } else if (response.responseJSON.error) {
                            errorMessages += "<li>" + response.responseJSON.error + "</li>";
                        } else {
                            errorMessages = 'Ocurrió un error inesperado.';
                        }

                        const alertDiv =
                            '<div class="alert alert-danger py-2"><ul class="no-bullets">' +
                            errorMessages + '</ul></div>';
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

        document.addEventListener('DOMContentLoaded', () => {
            const contenedorGrilla = document.getElementById('contenedor-grilla');
            const columnasExtra = document.querySelectorAll('.columna-extra');

            const ajustarColumnas = () => {
                const anchoGrilla = contenedorGrilla.offsetWidth;
                // console.log("paso por aca: ",anchoGrilla);
                if (anchoGrilla < 1250) {
                    columnasExtra.forEach(col => col.classList.add('d-none'));
                } else {
                    columnasExtra.forEach(col => col.classList.remove('d-none'));
                }
            };

            //             window.addEventListener('resize', () => {
            //     console.log('Se detectó un cambio en el tamaño de la ventana');
            // });

            window.addEventListener('resize', ajustarColumnas);
            const observer = new ResizeObserver(ajustarColumnas);
            observer.observe(contenedorGrilla);
            ajustarColumnas();
        });
    </script>  --}}
@endsection
