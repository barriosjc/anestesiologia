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
                            @if($tipo == 'todos')
                                <th>voto</th>
                            @endif
                            <th>Fecha</th>
                            <th>Puntos</th>
                            <th>Justificación</th>
                            <th>Opciones</th>
                            <th>Reconocidos</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th><a href='{{ route ('reconocimientos.exportar', 'todos')}}' type="button" class="bttn primary import" data-type="csv">Export CSV</a></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($realizados as $item)
                            <tr>
                                <td>{{ $item->enc_desc }}</td>
                                @if($tipo == 'todos')
                                    <td>{{$item->last_name}}</td>
                                @endif
                                <td>{{ $item->fecha_ingreso }}</td>
                                <td>{{ $item->puntos }}</td>
                                <td>{{ $item->observaciones }}</td>
                                <td>{{ $item->opciones_concat }}</td>
                                <td>{{ $item->votados_concat }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <a href='{{ route ('reconocimientos.exportar', 'todos')}}' type="button" class="bttn primary import" data-type="csv">Export XLSX</a>
            </div>
        </div>
    </div>

@endsection
