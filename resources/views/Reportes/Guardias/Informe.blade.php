<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            background-color: #eee;
            text-align: center;
        }

        .tabla-header,
        .tabla-header tr,
        .tabla-header td {
            border: none !important;
        }
    </style>
</head>

<body>

    <table class="tabla-header" width="100%" style="border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="text-align: left;">
                <h2>{{ $titulo }}</h2>
            </td>
            <td style="text-align: right;">
                <img src="{{ public_path('img/logo_medinexus_blanco.png') }}" alt="Logo" style="height: 70px;">
            </td>
        </tr>
    </table>

    <hr>

    <h3>Médicos y días hábiles</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 70%; text-align: left;">Médico</th>
                <th>Días hábiles</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($habiles as $fila)
                <tr>
                    <td style="text-align: left;">{{ $fila['medico'] }}</td>
                    <td>{{ $fila['habiles'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" style="text-align: center;">No hay guardias en días hábiles en el mes.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($incluir)
        <h3>Médicos y días feriados y fines de semana</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 70%; text-align: left;">Médico</th>
                    <th>Días feriados + findes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($feriadosFindes as $fila)
                    <tr>
                        <td style="text-align: left;">{{ $fila['medico'] }}</td>
                        <td>{{ $fila['feriados_findes'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="text-align: center;">No hay guardias en feriados ni fines de semana en el mes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <p style="font-size: 10px; margin-top: 20px; text-align: right;">Generado: {{ now()->format('d/m/Y H:i') }}</p>

</body>

</html>