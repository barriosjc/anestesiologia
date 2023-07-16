@extends('layouts.main')

@section('titulo', $titulo)
@section('contenido')
    <!-- Main page content-->
    <div class="container-fluid px-4">
        <header class="page-header page-header-dark pb-10"
        style="background-image: url({{ asset(Storage::disk('empresas')->url(session('empresa')->listado_reconocimientos ?? '')) }}); background-size: cover; background-repeat: no-repeat;">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="star"></i></div>
                            ¡reconocimientos!
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </header>
        <div class="card mb-4">
            <div class="card-header">{{$tipo == 'todos' ? 'Realizados' : 'Listado de reconocimientos'}}</div>
            <div class="card-body">
                <form method="GET" action="{{ route('reconocimientos.realizados', 'todos') }}" role="form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group input-group-sm">
                                <select name="periodo_id" class="form-control" id="periodo_id" {{ $tipo == 'periodo' ? 'disabled' : ''}}>
                                    @foreach ($periodos as $data)
                                        <option value="{{ $data->id }}" {{$periodo_id == $data->id ? 'selected' : ''}}>{{ $data->descripcion }}</option>
                                    @endforeach
                                </select>
                                @if($tipo != 'periodo')
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-primary btn-sm" type="submit">Filtrar</button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>Encuesta</th>
                            @if ($tipo == 'todos' || $tipo == 'periodo')
                                <th>voto</th>
                            @endif
                            <th>Fecha</th>
                            {{-- <th>Puntos</th> --}}
                            <th>Justificación</th>
                            <th>Valores</th>
                            <th>Reconocidos</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th><a href='{{ route('reconocimientos.exportar', 'todos') }}' type="button"
                                    class="bttn primary import" data-type="csv">Export CSV</a></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach ($realizados as $item)
                            <tr>
                                <td>{{ $item->enc_desc }}</td>
                                @if ($tipo == 'todos' || $tipo == 'periodo')
                                    <td><a href="{{ route('profile.readonly', $item->users_id) }}"
                                        title="Ir al perfil del usuario">{{ $item->last_name }}</a></td>
                                @endif
                                <td>{{ $item->fecha_ingreso }}</td>
                                {{-- <td>{{ $item->puntos }}</td> --}}
                                <td>{{ $item->observaciones }}</td>
                                <td>{{ $item->opciones_concat }}</td>
                                @php($sepa = '')
                                <td>   
                                @foreach (explode(',', $item->votados_concat) as $data)
                                {{$sepa}}
                                <a href="{{ route('profile.readonly', explode('|', $data)[1]) }}"
                                    title="Ir al perfil del usuario">{{ explode('|', $data)[0] }}</a>
                                    @php($sepa = ', ')
                                @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($tipo != 'periodo')
                <a href='{{ route('reconocimientos.exportar', 'todos') }}' type="button" class="bttn primary import"
                    data-type="csv">Export XLSX</a>
                @endif
            </div>
        </div>
    </div>

    <script>

    </script>
@endsection
