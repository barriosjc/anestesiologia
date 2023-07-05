@extends('layouts.main')

@section('titulo', 'Dashboard')
@section('contenido')
    <div class="container-fluid">
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
            <div class="col-lg-6 col-xl-3 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="me-3">
                                <div class="text-white-75 small">Usuarios activos</div>
                                <div class="text-lg fw-bold">123</div>
                            </div>
                            <i class="feather-xl text-white-50" data-feather="user"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between small">
                        <a class="text-white stretched-link" href="#!">View Report</a>
                        <div class="text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-xl-3 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="me-3">
                                <div class="text-white-75 small">Reconocimientos realizados</div>
                                <div class="text-lg fw-bold">265</div>
                            </div>
                            <i class="feather-xl text-white-50" data-feather="award"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between small">
                        <a class="text-white stretched-link" href="#!">View Report</a>
                        <div class="text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-xl-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="flex ">
                            <div class="input-group  input-group-sm mb-3">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value=""
                                        aria-label="Checkbox for following text input" checked>
                                </div>
                                <input type="text" class="form-control" value='Encuesta actual'
                                    aria-label="Text input with checkbox">
                            </div>

                            <div class="input-group input-group-sm">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" value=""
                                        aria-label="Radio button for following text input">
                                </div>
                                <input type="date" class="form-control"aria-label="Text input with radio button">
                                <input type="date" class="form-control" aria-label="Text input with radio button">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between small">
                        <a class="stretched-link" href="#!">Refresh</a>
                        {{-- <div><i class="fas fa-angle-right"></i></div> --}}
                    </div>
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
                                <td>{{ $item->last_name }}</td>
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

    {{-- <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Set new default font family and font color to mimic Bootstrap's default styling
            (Chart.defaults.global.defaultFontFamily = "Metropolis"),
            '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
            Chart.defaults.global.defaultFontColor = "#858796";

            function number_format(number, decimals, dec_point, thousands_sep) {
                // *     example: number_format(1234.56, 2, ',', ' ');
                // *     return: '1 234,56'
                number = (number + "").replace(",", "").replace(" ", "");
                var n = !isFinite(+number) ? 0 : +number,
                    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                    sep = typeof thousands_sep === "undefined" ? "," : thousands_sep,
                    dec = typeof dec_point === "undefined" ? "." : dec_point,
                    s = "",
                    toFixedFix = function(n, prec) {
                        var k = Math.pow(10, prec);
                        return "" + Math.round(n * k) / k;
                    };
                // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                s = (prec ? toFixedFix(n, prec) : "" + Math.round(n)).split(".");
                if (s[0].length > 3) {
                    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                }
                if ((s[1] || "").length < prec) {
                    s[1] = s[1] || "";
                    s[1] += new Array(prec - s[1].length + 1).join("0");
                }
                return s.join(dec);
            }

            var data = JSON.parse('{!!$data !!}')
            var label = JSON.parse('{!!$label !!}')
            console.log(label);
            console.log(data);
            // Area Chart Example
            var ctx = document.getElementById("myAreaChart");
            var myLineChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: label,
                    datasets: [{
                        label: "Cerca de",
                        lineTension: 0.3,
                        backgroundColor: "rgba(0, 97, 242, 0.05)",
                        borderColor: "rgba(0, 97, 242, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(0, 97, 242, 1)",
                        pointBorderColor: "rgba(0, 97, 242, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(0, 97, 242, 1)",
                        pointHoverBorderColor: "rgba(0, 97, 242, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: data
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            time: {
                                unit: "date"
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 7
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                                // Include a dollar sign in the ticks
                                callback: function(value, index, values) {
                                    return number_format(value);
                                }
                            },
                            gridLines: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }]
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        titleMarginBottom: 10,
                        titleFontColor: "#6e707e",
                        titleFontSize: 14,
                        borderColor: "#dddfeb",
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: "index",
                        caretPadding: 10,
                        callbacks: {
                            label: function(tooltipItem, chart) {
                                var datasetLabel =
                                    chart.datasets[tooltipItem.datasetIndex].label || "";
                                return datasetLabel + ": " + number_format(tooltipItem.yLabel);
                            }
                        }
                    }
                }
            });

            console.log(myLineChart)
        });
    </script> --}}
@endsection
