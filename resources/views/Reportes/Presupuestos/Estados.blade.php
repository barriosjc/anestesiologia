<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Listado de Presupuestos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        @page {
            margin: 0.5cm 0.5cm;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            margin: 0.5cm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            padding: 3px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-end {
            text-align: right;
        }
    </style>
</head>

<body>

    <h3>Detalle de presupuestos - Fechas de cirugía : {{ $fec_desde }} al {{ $fec_hasta }}</h3>

    @php
        $groupedProf = $datos->groupBy(fn($item) => optional($item->profesional)->nombre);
    @endphp

    @foreach ($groupedProf as $prof_nombre => $profGroup)

        <div style="width: 100%; margin-top: 15px;">

            <table class="table table-striped">

                {{-- Línea separadora --}}
                <tr>
                    <td colspan="8">
                        <hr style="border: 1px solid #ddd; margin: 2px 0;">
                    </td>
                </tr>

                {{-- Header Profesional + Total --}}
                <tr>
                    <td colspan="6">
                        <strong>Profesional: {{ $prof_nombre ?? 'Sin profesional' }}</strong>
                    </td>
                    <td class="text-end">
                        <strong>
                            {{ number_format(
                                $profGroup->sum(fn($item) => $item->presupuestosDet->sum('valor')),
                                2, ',', '.'
                            ) }}
                        </strong>
                    </td>
                    <td></td>
                </tr>

                {{-- Línea separadora --}}
                <tr>
                    <td colspan="8">
                        <hr style="border: 1px solid #ddd; margin: 2px 0;">
                    </td>
                </tr>

                <thead>
                    <tr>
                        <th style="width:6%">Nro</th>
                        <th style="width:10%">Fecha</th>
                        <th style="width:25%">Nombre</th>
                        <th style="width:12%">DNI</th>
                        <th style="width:12%">F. Nac</th>
                        <th style="width:15%">Cobertura</th>
                        <th style="width:10%" class="text-end">Valor ($)</th>
                        <th style="width:10%">Estado</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($profGroup as $presupuesto)

                        @php
                            $valorTotal = $presupuesto->presupuestosDet->sum('valor');
                        @endphp

                        <tr>
                            <td>{{ $presupuesto->id }}</td>
                            <td>{{ $presupuesto->fecha }}</td>
                            <td>{{ $presupuesto->nombre }}</td>
                            <td class="text-end">{{ $presupuesto->dni }}</td>
                            <td class="text-end">
                                {{ $presupuesto->fecha_nac 
                                    ? \Carbon\Carbon::parse($presupuesto->fecha_nac)->format('d/m/Y') 
                                    : '' }}
                            </td>
                            <td>
                                {{ optional($presupuesto->presupuestosDet->first())->cobertura_id }}
                            </td>
                            <td class="text-end">
                                {{ number_format($valorTotal, 2, ',', '.') }}
                            </td>
                            <td>{{ $presupuesto->estado }}</td>
                        </tr>

                    @endforeach
                </tbody>

            </table>
        </div>

    @endforeach

    {{-- TOTAL GENERAL --}}
    <div class="text-end" style="margin-top: 20px; margin-right: 50px;">
        <h4 style="font-weight: bold;">
            Total General : $
            {{ number_format(
                $datos->sum(fn($item) => $item->presupuestosDet->sum('valor')),
                2, ',', '.'
            ) }}
        </h4>
    </div>

</body>

</html>