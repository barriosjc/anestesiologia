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

        .text-end {
            text-align: right;
        }
    </style>
</head>

<body>

    <table class="tabla-header" width="100%" style="border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="text-align: left;">
                <h2>Valorización</h2>
                <div style="font-size: 11px;">
                    @if (!empty($filtros['grupo']))
                        <strong>Grupo:</strong> {{ $filtros['grupo'] }}
                    @endif
                    @if (!empty($filtros['nivel']))
                        @if (!empty($filtros['grupo'])) &nbsp;|&nbsp; @endif
                        <strong>Nivel:</strong> {{ $filtros['nivel'] }}
                    @endif
                </div>
            </td>
            <td style="text-align: right;">
                <img src="{{ public_path('img/logo_medinexus_blanco.png') }}" alt="Logo" style="height: 70px;">
            </td>
        </tr>
    </table>

    <hr>

        <table>
        <thead>
            <tr>
                <th>Nivel</th>
                <th>Valor</th>
                <th>Moneda</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($valores as $item)
                <tr>
                    <td style="text-align: center;">{{ $item->nivel }}</td>
                    <td class="text-end">{{ number_format((float) $item->valor, 2, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $item->moneda }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No se han encontrado datos para los filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p style="font-size: 10px; margin-top: 20px; text-align: right;">Generado: {{ now()->format('d/m/Y H:i') }}</p>

</body>

</html>