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
        <h3>Listado de producción desde : {{ $parametros["fec_desde_adm"] }} hasta {{ $parametros["fec_hasta_adm"] }} </h3>
        @php
            $grupoUsers = $datos->groupBy('name');
        @endphp


        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width:10%">Fecha Carga</th>
                    <th style="width:40%">cantidad</th>
        
                </tr>
            </thead>
            @foreach ($grupoUsers as $name => $usuarios)
                <tr>
                    <td colspan="6">
                        <hr style="border: 1px solid #ddd; margin: 0;">
                    </td>
                </tr>
                <tr class="custom-font">
                    <td colspan="4"><h4>{{ $name }}</h4></td>
                    <td class="text-end w-5"><h4>{{ number_format((float) $usuarios->sum('cantidad') , 0, ',', '.') }}</h4></td>
                </tr>
                <tr>
                    <td colspan="6">
                        <hr style="border: 1px solid #ddd; margin: 0;">
                    </td>
                </tr>             
                <tbody>
                    @foreach ($usuarios as $item)
                        <tr> 
                            <td>{{ $item->fecha }}</td>
                            <td>{{ $item->cantidad }}</td>
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
                <td colspan="5" class="text-end"><h4> Total : {{ number_format((float) $datos->sum('cantidad') , 0, ',', '.') }}</h4></td>
            </tr>
        </table>
    </div>
</body>

</html>
