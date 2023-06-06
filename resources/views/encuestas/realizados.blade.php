@extends('layouts.main')

@section('titulo', $titulo)
@section('contenido')
    <!-- Main page content-->
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">Realizados</div>
            <div class="card-body">
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>Encuesta</th>
                            <th>Fecha</th>
                            <th>Puntos</th>
                            <th>Observaciones</th>
                            <th>Opciones</th>
                            <th>Reconocidos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($realizados as $item)
                            <tr>
                                <td></td>
                                
                                <td>{{ $item->fecha_ingreso }}</td>
                                <td>{{ $item->puntos }}</td>
                                <td>{{ $item->observaciones }}</td>
                                <td>{{ $item->opciones_concat }}</td>
                                <td>{{ $item->votados_concat }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
