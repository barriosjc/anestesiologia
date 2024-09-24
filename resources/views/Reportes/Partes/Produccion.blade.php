<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Listado de Consumos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            margin-left: 0.5cm;
            margin-right: 0;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            margin-left: 0.5cm;
            margin-right: 0;
        }
        .custom-font {
            font-family: 'Courier', monospace;
            font-size: 14px;
        }
        td {
            padding-right: 6px; 
        }
    </style>
</head>

<body>
    <div class="container">
        <h3>Listado de producción de : {{ $consumos[0]->prof_nombre }} periodo: {{ $consumos[0]->periodo }}</h3>
        @php
            $groupedConsumos = $consumos->groupBy('cen_nombre');
        @endphp


        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width:10%">F.prestación</th>
                    <th style="width:40%">Paciente</th>
                    <th style="width:20%">Cobertura</th>
                    <th style="width:20%">Nivel</th>
                    <th style="width:10%">Valor</th>
                </tr>
            </thead>
            @foreach ($groupedConsumos as $cen_nombre => $consumosGroup)
                <tr>
                    <td colspan="6">
                        <hr style="border: 1px solid #ddd; margin: 0;">
                    </td>
                </tr>
                <tr class="custom-font">
                    <td colspan="4"><h4>{{ $cen_nombre }}</h4></td>
                    <td class="text-end w-5"><h4>{{ number_format((float) $consumosGroup->sum('valor') , 2, ',', '.') }}</h4></td>
                </tr>
                <tr>
                    <td colspan="6">
                        <hr style="border: 1px solid #ddd; margin: 0;">
                    </td>
                </tr>             
                <tbody>
                    @foreach ($consumosGroup as $consumo)
                        <tr>
                            <td>{{ $consumo->fec_prestacion }}</td>
                            <td>{{ $consumo->pac_nombre }}</td>
                            <td>{{ $consumo->cob_sigla }}</td>
                            <td>{{ $consumo->niveles }}</td>
                            <td class="custom-width-valor text-end">{{ number_format((float) $consumo->valor, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            @endforeach
            <tr>
                <td colspan="6">
                    <hr style="border: 1px solid #ddd; margin: 0;">
                </td>
            </tr>        
            <tr class="custom-font">
                <td colspan="5" class="text-end"><h4> Total : $ {{ number_format((float) $consumos->sum('valor') , 2, ',', '.') }}</h4></td>
            </tr>
        </table>
    </div>
</body>

</html>
