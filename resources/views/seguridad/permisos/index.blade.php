@extends('layouts.main')

@section('titulo', 'Seguridad')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                                <span>
                                    Permisos {{ isset($titulo) ? $titulo : '' }}
                                </span>

                                @if ($esabm === false)
                                    @if ($padre === 'usuarios')
                                        <a href="{{ url('/usuario') }}" class="btn btn-warning btn-sm float-right">
                                            <i class="fa fa-arrow-left "
                                                    aria-hidden="true"></i> Volver</a>
                                    @else
                                        <a href="{{ url()->previous() }}" class="btn btn-warning btn-sm float-right">
                                            <i class="fa fa-arrow-left "
                                                    aria-hidden="true"></i> Volver</a>
                                    @endif
                                @else
                                    <a href="{{ url('/permisos/create') }}" class="btn btn-primary btn-sm float-right"
                                        title="Agregar nuevo Permiso">
                                        <i class="fa fa-plus" aria-hidden="true"></i> Agregar nuevo
                                    </a>
                                @endif
                        </div>
                        <div class="card-body">
                            @if ($padre === "permisos")
                            <form method="GET" action="{{ url('/permisos') }}" accept-charset="UTF-8"
                                class="form-inline my-2 my-lg-0 float-right" role="search">
                                <div class="input-group">
                                    <input type="text" class="form-control w-75" name="search" placeholder="Buscar..."
                                        value="{{ request('search') }}">
                                    <span class="input-group-append">
                                        <button class="btn btn-info" type="submit">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </span>
                                </div>
                            </form>
                            @endif
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Guard name</th>
                                            <th>Fecha Alta</th>
                                            <th class="float-right" style="width:25%">
                                                <div >
                                                    @if ($esabm)
                                                        Quitar
                                                    @else
                                                        Opciones
                                                    @endif
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($permisos as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->guard_name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                                                @if ($esabm)
                                                    <td>
                                                        <div class="float-right">
                                                            <a href="{{ url('/permisos/' . $item->id) . '/usuarios' }}"
                                                                data-bs-toggle="tooltip" title="Permisos asignados al usuario"><button
                                                                    class="btn btn-warning btn-sm"><i class="fa fa-users"
                                                                        aria-hidden="true"></i></button></a>
                                                            <a href="{{ url('/permisos/' . $item->id) . '/roles' }}"
                                                                data-bs-toggle="tooltip" title="Permisos asignados al rol"><button
                                                                    class="btn btn-success btn-sm"><i class="fa fa-key"
                                                                        aria-hidden="true"></i></button></a>
                                                            <a href="{{ url('/permisos/' . $item->id) }}"
                                                                data-bs-toggle="tooltip" title="Ver permiso"><button class="btn btn-info btn-sm"><i
                                                                        class="fa fa-fw fa-eye"
                                                                        aria-hidden="true"></i></button></a>
                                                            <a href="{{ url('/permisos/' . $item->id . '/edit') }}"
                                                                data-bs-toggle="tooltip" title="Modificar permiso"><button
                                                                    class="btn btn-primary btn-sm"><i
                                                                        class="far fa-edit"></i></button></a>

                                                            <form method="POST"
                                                                action="{{ url('/permisos' . '/' . $item->id) }}"
                                                                accept-charset="UTF-8" style="display:inline">
                                                                {{ method_field('DELETE') }}
                                                                {{ csrf_field() }}
                                                                <button type="submit" class="btn btn-danger btn-sm"
                                                                    data-bs-toggle="tooltip" title="Borrar permiso"
                                                                    onclick="return confirm('Confirma eliminar?')"><i
                                                                        class="far fa-trash-alt text-white"></i></button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                @else
                                                    <td>
                                                        <div class="float-right">
                                                            @if ($padre === 'roles')
                                                                <a href="{{ url('/roles/' . $rolid . '/permisos/' . $item->id . '/desasignar') }}"
                                                                    data-bs-toggle="tooltip" title="Quitar permiso asignado al rol"><button
                                                                        class="btn btn-danger btn-sm"><i
                                                                            class="fa fa-minus text-white"
                                                                            aria-hidden="true"></i></button></a>
                                                            @else
                                                                <a href="{{ url('/usuario/' . $usuid . '/permisos/' . $item->id . '/desasignar') }}"
                                                                    data-bs-toggle="tooltip" title="Quitar permiso asignados al usuario"><button
                                                                        class="btn btn-danger btn-sm"><i
                                                                            class="fa fa-minus text-white"
                                                                            aria-hidden="true"></i></button></a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{-- <div class="pagination-wrapper"> {!! $permisos->appends(['search' => Request::get('search')])->render() !!} </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($esabm === false)
                @include('seguridad.permisos.asignar')
            @endif
        </div>
    </div>
@endsection
