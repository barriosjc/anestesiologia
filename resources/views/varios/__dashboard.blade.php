@extends('layouts.main')

@section('titulo', 'Dashboard')
@section('contenido')
    <div class="container-fluid">
        <style>
            .styled-link {
                background: none;
                border: none;
                color: blue;
                cursor: pointer;
                text-decoration: underline;
                transition: color 0.3s, transform 0.3s;
            }

            .styled-link:hover {
                color: rgb(116, 111, 189);
                transform: scale(1.1);
            }
        </style>
        {{-- <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
            <div class="container-xl px-4">
                <div class="page-header-content pt-4">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mt-4">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="activity"></i></div>
                                Dashboard
                            </h1>
                            <div class="page-header-subtitle">Example dashboard overview and content summary</div>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <div class="input-group input-group-joined border-0" style="width: 16.5rem">
                                <span class="input-group-text"><i class="text-primary" data-feather="calendar"></i></span>
                                <input class="form-control ps-0 pointer" id="litepickerRangePlugin"
                                    placeholder="Select date range..." />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header> --}}
        <!-- Main page content-->

        <!-- Example Colored Cards for Dashboard Demo-->
        <div class="row">
            <div class="col-lg-6 col-xl-6 mb-4">
                <form method="GET" action="{{ route('dashboard.cambio') }}" accept-charset="UTF-8">
                    <div class="card h-100">
                        <div class="card-body">
                            {{-- @dd($option, $desde, $hasta) --}}
                            <div class="flex">
                                <div class="input-group  input-group-sm mb-3">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0" type="radio" value="actual"
                                            {{$option == 'actual' ? 'checked' : ''}}
                                            aria-label="Checkbox for following text input" name="radio-group">
                                    </div>
                                    <input type="text" class="form-control" value='Votación actual'
                                        aria-label="Text input with checkbox">
                                </div>

                                <div class="input-group input-group-sm">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0" type="radio" value="perso"
                                            {{$option == 'perso' ? 'checked' : ''}}
                                            aria-label="Radio button for following text input" name="radio-group">
                                    </div>
                                    <input type="date" name="desde" class="form-control"
                                        value={{$desde}}
                                        aria-label="Text input with radio button">
                                    <input type="date" name="hasta" class="form-control"
                                        value={{$hasta}}
                                        aria-label="Text input with radio button">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between small">
                            <input type="submit" class="styled-link" value="Refresh">
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-6 col-xl-3 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="me-3">
                                <div class="text-white-75 small">Usuarios activos</div>
                                <div class="text-lg fw-bold">{{ $cant_usu }}</div>
                            </div>
                            <i class="feather-xl text-white-50" data-feather="user"></i>
                        </div>
                    </div>
                    {{-- <div class="card-footer d-flex align-items-center justify-content-between small">
                        <a class="text-white stretched-link" href="#!">View Report</a>
                        <div class="text-white"><i class="fas fa-angle-right"></i></div>
                    </div> --}}
                </div>
            </div>
            <div class="col-lg-6 col-xl-3 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="me-3">
                                <div class="text-white-75 small">Reconocimientos realizados</div>
                                <div class="text-lg fw-bold">{{ $cant_recon }}</div>
                            </div>
                            <i class="feather-xl text-white-50" data-feather="award"></i>
                        </div>
                    </div>
                    {{-- <div class="card-footer d-flex align-items-center justify-content-between small">
                        <a class="text-white stretched-link" href="#!">View Report</a>
                        <div class="text-white"><i class="fas fa-angle-right"></i></div>
                    </div> --}}
                </div>
            </div>
        </div>
        <!-- Example Charts for Dashboard Demo-->
        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="card card-header-actions h-100">
                    <div class="card-header">
                        Cantidad de participantes
                        {{-- <div class="dropdown no-caret">
                            <button class="btn btn-transparent-dark btn-icon dropdown-toggle" id="areaChartDropdownExample"
                                type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
                                    class="text-gray-500" data-feather="more-vertical"></i></button>
                            <div class="dropdown-menu dropdown-menu-end animated--fade-in-up"
                                aria-labelledby="areaChartDropdownExample">
                                <a class="dropdown-item" href="#!">&Uacute;ltimos 12 meses</a>
                                <a class="dropdown-item" href="#!">&Uacute;ltimos 6 meses</a>
                                <a class="dropdown-item" href="#!">&Uacute;ltimos 3 meses</a>
                                <a class="dropdown-item" href="#!">Encuesta actual</a>
                                <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#!">Custom Range</a>
                            </div>
                        </div> --}}
                    </div>
                    <div class="card-body">
                        <div class="chart-area"><canvas id="myAreaChart" width="100%" height="30"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 mb-4">
                <div class="card card-header-actions h-100">
                    <div class="card-header">
                        Ranking de valores
                        {{-- <div class="dropdown no-caret">
                            <button class="btn btn-transparent-dark btn-icon dropdown-toggle" id="areaChartDropdownExample"
                                type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
                                    class="text-gray-500" data-feather="more-vertical"></i></button>
                            <div class="dropdown-menu dropdown-menu-end animated--fade-in-up"
                                aria-labelledby="areaChartDropdownExample">
                                <a class="dropdown-item" href="#!">&Uacute;ltimos 12 meses</a>
                                <a class="dropdown-item" href="#!">&Uacute;ltimos 6 meses</a>
                                <a class="dropdown-item" href="#!">&Uacute;ltimos 3 meses</a>
                                <a class="dropdown-item" href="#!">Encuesta actual</a>
                                <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#!">Custom Range</a>
                            </div>
                        </div> --}}
                    </div>
                    <div class="card-body">
                        <div class="chart-bar"><canvas id="myBarChart" width="100%" height="30"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Example DataTable for Dashboard Demo-->
        <div class="card mb-4">
            <div class="card-header">Ranking de colaboradores</div>
            <div class="card-body">
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Area</th>
                            <th>Reconocimientos</th>
                            <th>Puntos</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($valores as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->cargo }}</td>
                                <td>{{ $item->area }}</td>
                                <td>{{ $item->cant }}</td>
                                <td>{{ $item->puntos }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/dashboard/chart-area.js') }}" data-data="{{ $data }}"
        data-label="{{ $label }}"></script>
     <script src="{{ asset('js/dashboard/chart-bar.js') }}" opciones-data="{{ $opciones_data }}"
        opciones-label="{{ $opciones_label }}"></script>

    <script>
        const actualRadio = document.querySelector('input[value="actual"]');
        const desdeInput = document.querySelector('input[name="desde"]');
        const hastaInput = document.querySelector('input[name="hasta"]');

        // desdeInput.disabled = true;
        // hastaInput.disabled = true;
        // desdeInput.value = '';
        // hastaInput.value = '';

        // actualRadio.addEventListener('change', () => {
        //     desdeInput.disabled = true;
        //     hastaInput.disabled = true;
        //     desdeInput.value = '';
        //     hastaInput.value = '';
        // });

        // const persoRadio = document.querySelector('input[value="perso"]');
        // persoRadio.addEventListener('change', () => {
        //     desdeInput.disabled = false;
        //     hastaInput.disabled = false;
        // });
    </script> 

@endsection
