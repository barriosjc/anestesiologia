<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; }
        .tabla-header, .tabla-header tr, .tabla-header td, .tabla-header th {border: none !important;}
    </style>
</head>
<body>

<body>

<table class="tabla-header" width="100%" style="border-collapse: collapse; margin-bottom: 20px;">

    <tr>
        <td style="text-align: left;">
            {{-- Dejar vacío para equilibrar --}}
        </td>
        <td style="text-align: right;">
            <img src="{{ public_path('img/logo_medinexus_blanco.png') }}" alt="Logo" style="height: 70px;">
        </td>
    </tr>

    <tr>
        <td><h3><strong>Presupuesto Nº:</strong> {{ $presupuesto->id }}</h3></td>
        <td><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($presupuesto->fecha)->format('d/m/Y') }}</td>
    </tr>

    <tr>
        <td><strong>Paciente:</strong> {{ $presupuesto->paciente }}</td>
        <td><strong>Profesional:</strong> {{ $presupuesto->user->name ?? '---' }}</td>
    </tr>

    <tr>
        <td><strong>Centro:</strong> {{ $presupuesto->centro->nombre ?? '---' }}</td>
        <td><strong>Cargado por:</strong> {{ $presupuesto->user->name ?? '---' }}</td>
    </tr>

    <tr>
        <td><h3><strong>Total Presupuesto:</strong> ${{ number_format($presupuesto->presupuestosDet->sum('valor'), 2, ',', '.') }}</h3></td>
        <td><h3><strong>Total Pagado:</strong> ${{ number_format($presupuesto->pagos->sum('valor'), 2, ',', '.') }}</h3></td>
    </tr>
</table>

<hr>

<h3>Detalle de Prácticas</h3>
<table>
    <thead>
        <tr>
            <th>Descripción</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        @foreach($presupuesto->presupuestosDet as $det)
        <tr>
            <td>{{ $det->descripcion }}</td>
            <td style="text-align: right;">$ {{ number_format($det->valor, 2, ',', '.') }}</td>
        </tr>
        @endforeach
        @if($presupuesto->presupuestosDet->count() === 0 )
            <td colspan="3"  style="text-align: center;">Sin prácticas cargadas en el presupuesto</td>
        @endif
    </tbody>
</table>

{{-- <hr> --}}

<h3>Pagos Realizados</h3>
<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Cobró</th>
            <th style="text-align: right;">Valor</th>
        </tr>
    </thead>
    <tbody>
        @foreach($presupuesto->pagos as $pago)
        <tr>
            <td>{{  \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}</td>
            <td>{{ $pago->user->name }} </td>
            <td style="text-align: right;">$ {{ number_format($pago->valor, 2, ',', '.') }}</td>
        </tr>
        @endforeach
        @if($presupuesto->pagos->count() === 0 )
            <td colspan="3"  style="text-align: center;">Sin pagos realizados</td>
        @endif
    </tbody>
</table>

{{-- <p><strong>Total:</strong> ${{ number_format($presupuesto->presupuestosDet->sum('valor'), 2, ',', '.') }}</p>
<p><strong>Total Pagado:</strong> ${{ number_format($presupuesto->pagos->sum('valor'), 2, ',', '.') }}</p> --}}

</body>
</html>
