@extends('layouts.main')

@section('titulo', 'Seguridad')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            Roles {{ isset($titulo) ? $titulo : '' }}
                            @if ($esabm === false)
                                @if ($padre === 'usuarios')
                                    <a href="{{ url('/usuario') }}" class="btn btn-warning btn-sm float-right"><i
                                                class="fa fa-arrow-left" aria-hidden="true"></i> Volver</a>
                                @else
                                    <a href="{{ url()->previous() }}" class="btn btn-warning btn-sm float-right"><i
                                                class="fa fa-arrow-left" aria-hidden="true"></i> Volver</a>
                                @endif
                            @else
                                <a href="{{ url('/roles/create') }}" class="btn btn-success btn-sm float-right"
                                    title="Agregar nuevo Role">
                                    <i class="fa fa-plus" aria-hidden="true"></i> Agregar nuevo
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            @if ($esabm === true)
                                 <form method="GET" action="{{ url('/roles') }}" accept-charset="UTF-8"
                                    class="form-inline my-2 my-lg-0 float-right" role="search">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" placeholder="Buscar..."
                                            value="{{ request('search') }}">
                                        <span class="input-group-append">
                                            <button class="btn btn-info" type="submit">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </span>
                                    </div>
                                </form>
                            @endif
                            {{-- <br /> --}}
                            {{-- <br /> --}}
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Guard name</th>
                                            <th>Fecha Alta</th>
                                            <th class="float-right" style="width:25%">
                                                Valores
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($roles as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->guard_name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                                                @if ($esabm)
                                                    <td>
                                                        <div class="float-right">
                                                            <a href="{{ url('/roles/' . $item->id . '/usuarios') }}"
                                                                data-bs-toggle="tooltip" title="Usuarios asignados al grupo"><button
                                                                    class="btn btn-warning btn-sm"><i class="fa fa-users"
                                                                        aria-hidden="true"></i></button></a>
                                                            <a href="{{ url('/roles/' . $item->id . '/permisos') }}"
                                                                data-bs-toggle="tooltip" title="Permisos asignados al grupo"><button
                                                                    class="btn btn-success btn-sm"><i class="fa fa-key"
                                                                        aria-hidden="true"></i></button></a>
                                                            <a href="{{ url('/roles/' . $item->id) }}"
                                                                data-bs-toggle="tooltip" title="View Role"><button class="btn btn-info btn-sm"><i
                                                                        class="fa fa-eye"
                                                                        aria-hidden="true"></i></button></a>
                                                            <a href="{{ url('/roles/' . $item->id . '/edit') }}"
                                                                data-bs-toggle="tooltip" title="Editar role"><button class="btn btn-primary btn-sm"><i
                                                                        class="far fa-edit"></i></button></a>

                                                            <form method="POST"
                                                                action="{{ url('/roles' . '/' . $item->id) }}"
                                                                accept-charset="UTF-8" style="display:inline">
                                                                {{ method_field('DELETE') }}
                                                                {{ csrf_field() }}
                                                                <button type="submit" class="btn btn-danger btn-sm"
                                                                data-bs-toggle="tooltip" title="Delete Role"
                                                                    onclick="return confirm('Confirma eliminar?')"><i
                                                                        class="far fa-trash-alt text-white"></i></button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                @else
                                                    <td>
                                                        <div class="float-right">
                                                            @if ($padre === 'usuarios')
                                                                <a href="{{ url('/usuario/' . $usuid . '/roles/' . $item->id . '/desasignar') }}"
                                                                    data-bs-toggle="tooltip" title="Quitar Rol asignados al usuario"><button
                                                                        class="btn btn-danger btn-sm"><i
                                                                            class="fa fa-minus text-white"
                                                                            aria-hidden="true"></i></button></a>
                                                            @else
                                                                <a href="{{ url('/permisos/' . $perid . '/roles/' . $item->id . '/desasignar') }}"
                                                                    data-bs-toggle="tooltip" title="Quitar el permiso asignados al rol"><button
                                                                        class="btn btn-danger btn-sm"><i
                                                                            class="fa fa-minus text-white"
                                                                            aria-hidden="true"
                                                                            onclick="return confirm('Confirma eliminar?')"></i></button></a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{-- {{ $roles->links() }} --}}
                                {{-- <div class="pagination-wrapper"> {{ $roles->appends(Request::all())->render() }} </div> --}}
                                {{-- <div class="pagination-wrapper"> {!! $roles->appends(['search' => Request::get('search')])->render() !!} </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($esabm === false)
                @include('seguridad.roles.asignar')
            @endif
        </div>
    </div>
@endsection
