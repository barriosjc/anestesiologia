@extends('layouts.main')

@section('titulo', $titulo)
@section('contenido')
    <!-- Main page content-->
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">Recibidos, puntos acumulados: {{array_sum(array_column($recibidos, 'puntos'));}}</div>
            <div class="card-body">
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>Encuesta</th>
                            <th>Fecha</th>
                            <th>Puntos</th>
                            <th>Observaciones</th>
                            <th>Opciones</th>
                            <th>Recibidos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recibidos as $item)
                            <tr>
                                <td></td>
                                <td>{{ $item->fecha_ingreso }}</td>
                                <td>{{ $item->puntos }}</td>
                                <td>{{ $item->observaciones }}</td>
                                <td>{{ $item->opciones_concat }}</td>
                                <td>{{ $item->last_name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
