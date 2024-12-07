<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Listado de Consumos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            margin: 0.5cm 0.5cm;
            /* Márgenes pequeños para usar más espacio horizontal */
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            margin: 0.5cm;
        }

        .custom-font {
            font-family: 'Courier', monospace;
            font-size: 14px;
        }

        table {
            width: 100%;
            /* Ocupa todo el ancho disponible */
            border-collapse: collapse;
            /* Para que los bordes no se dupliquen */
            table-layout: fixed;
            /* Fuerza que las celdas ocupen el ancho de la tabla */
        }

        th,
        td {
            padding: 2px;
            /* border: 1px solid black; Añade bordes visibles para cada celda */
            text-align: left;
            /* word-wrap: break-word; */
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>


<body>
    {{-- <div class="container" style="width: 100%;"> --}}
    <h3>Detalle de prestaciones:periodo: {{ $fec_desde }} al {{ $fec_hasta }} </h3>
    @php
        $groupedProf = $datos->groupBy('prof_nombre');
    @endphp
    @foreach ($groupedProf as $prof_nombre => $profGroup)
    {{-- <div style="page-break-after: always; width: 100%;"> --}}
        <div style="width: 100%;">

            <h3>Profesional: {{ $prof_nombre }} </h3>

            @php
                $groupedConsumos = $profGroup->groupBy('cen_nombre');
            @endphp

            <table class="table table-striped">
                <thead>
                    <tr>
@if ($conObs)
<th style="width:7%">Nro Parte</th>
<th style="width:8%">Fecha qx</th>
<th style="width:20%">Paciente</th>
<th style="width:9%">Cobertura</th>
<th style="width:7%">Periodo</th>
<th style="width:12%">Práctica</th>
<th style="width:4%">%</th>
<th style="width:7%">Valor($)</th>
<th style="width:10%">Estado</th>
<th style="width:20%">Observaciones</th>
@else
<th style="width:10%">Nro Parte</th>
<th style="width:8%">Fecha qx</th>
<th style="width:27%">Paciente</th>
<th style="width:9%">Cobertura</th>
<th style="width:7%">Periodo</th>
<th style="width:20%">Práctica</th>
<th style="width:4%">%</th>
<th style="width:10%">Valor($)</th>
<th style="width:10%">Estado</th>
@endif
                    </tr>
                </thead>
                @foreach ($groupedConsumos as $cen_nombre => $consumosGroup)
                    <tr>
                        @if ($conObs)
                        <td colspan="10" >
                        @else 
                        <td colspan="9" >
                        @endif
                            <hr style="border: 1px solid #ddd; margin: 0;">
                        </td>
                    </tr>
                    <tr class="custom-font">
                        <td colspan="7">
                            <h4>{{ $cen_nombre }}</h4>
                        </td>
                        <td class="text-end w-5">
                            <h4>{{ number_format((float) $consumosGroup->sum('valor'), 2, ',', '.') }}</h4>
                        </td>
                    </tr>
                    <tr>
                        @if ($conObs)
                        <td colspan="10" >
                        @else 
                        <td colspan="9" >
                        @endif
                            <hr style="border: 1px solid #ddd; margin: 0;">
                        </td>
                    </tr>
                    <tbody>
                        @foreach ($consumosGroup as $consumo)
                            <tr>
                                <td>{{ $consumo->parte_cab_id }}</td>
                                <td>{{ $consumo->fec_prestacion }}</td>
                                <td>{{ $consumo->pac_nombre }}</td>
                                <td>{{ $consumo->cob_sigla }}</td>
                                <td>{{ $consumo->periodo }}</td>
                                <td>{{ $consumo->nivel . '/' . $consumo->nom_descripcion }}</td>
                                <td>{{ $consumo->porcentaje }}</td>
                                <td class="custom-width-valor text-end">
                                    {{ number_format((float) $consumo->valor, 2, ',', '.') }}</td>
                                <td>{{ $consumo->est_descripcion }}</td>
                                @if ($conObs)
                                    <td>{{ $consumo->obs_refac }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach
                <tr>
                    @if ($conObs)
                    <td colspan="10" >
                    @else 
                    <td colspan="9" >
                    @endif
                        <hr style="border: 1px solid #ddd; margin: 0;">
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
    <div class="custom-font text-end" style="margin-top: 10px;margin-right: 50px;">
        <h4>Total : $ {{ number_format((float) $datos->sum('valor'), 2, ',', '.') }}</h4>
    </div>
    {{-- </div> --}}
</body>

</html>
