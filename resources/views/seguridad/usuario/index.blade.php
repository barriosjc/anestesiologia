@extends('layouts.main')

@section('titulo', 'Usuarios')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <span id="card_title">
                                    Usuarios {{ isset($titulo) ? $titulo : '' }}
                                </span>
                                @if ($esabm != false)
                                    <a href="{{ url('/usuario/create') }}" class="btn btn-primary btn-sm float-right"
                                        title="Agregar nuevo Usuario">
                                        <i class="fa fa-plus" aria-hidden="true"></i> Agregar nuevo
                                    </a>
                                @else
                                    @if ($padre === 'roles')
                                        <a href="{{ url('/roles') }}" 
                                                class="btn btn-warning btn-sm float-right"><i class="fas fa-arrow-left"
                                                    aria-hidden="true"></i> Volver</a>
                                    @else
                                        <a href="{{ url('/permisos') }}" 
                                                class="btn btn-warning btn-sm float-right"><i class="fas fa-arrow-left"
                                                    aria-hidden="true"></i> Volver</a>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($esabm === true)
                                <form method="GET" action="{{ url('/usuario') }}" accept-charset="UTF-8"
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

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Usuario</th>
                                            <th>Avatar</th>
                                            <th>Mail</th>
                                            <th class="float-right" style="width:25%">
                                                Valores
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($user as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td><img src="{{ Storage::disk('usuarios')->url($item->foto) }}"
                                                        class="rounded-circle" width="45px" alt=""> </td>

                                                <td>{{ $item->email }}</td>
                                                @if ($esabm)
                                                    <td>
                                                        <div class="float-right">
                                                            <div class="btn-group btn-group-sm" role="group"
                                                                aria-label="Basic example">
                                                                <a href="{{ url('/usuario/' . $item->id . '/roles') }}"><button
                                                                        class="btn btn-warning btn-sm"
                                                                        data-bs-toggle="tooltip"
                                                                        title="Roles asignados al usuario."><i
                                                                            class="fa fa-users"
                                                                            aria-hidden="true"></i></button></a>
                                                                <a href="{{ url('/usuario/' . $item->id . '/permisos') }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Permisos asignados al usuario "><button
                                                                        class="btn btn-success btn-sm"><i class="fa fa-key"
                                                                            aria-hidden="true"></i></button></a>
                                                                <a href="{{ url('/usuario/' . $item->id) }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Consultar Usuario"><button
                                                                        class="btn btn-info btn-sm"><i
                                                                            class="fa fa-fw fa-eye"
                                                                            aria-hidden="true"></i></button></a>
                                                                <a href="{{ url('/usuario/' . $item->id . '/edit') }}"
                                                                    data-bs-toggle="tooltip" title="Editar Usuario"><button
                                                                        class="btn btn-primary btn-sm"><i
                                                                            class="far fa-edit"></i></button></a>

                                                                <form method="POST"
                                                                    action="{{ url('/usuario' . '/' . $item->id) }}"
                                                                    accept-charset="UTF-8" style="display:inline">
                                                                    {{ method_field('DELETE') }}
                                                                    {{ csrf_field() }}
                                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                                        data-bs-toggle="tooltip" title="Borrar Usuario"
                                                                        onclick="return confirm('Confirma eliminar?')"><i
                                                                            class="far fa-trash-alt text-white"></i></button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </td>
                                                @else
                                                    <td>
                                                        <div class="float-right">
                                                            @if ($padre === 'roles')
                                                                <a href="{{ url('/roles/' . $rolid . '/usuarios/' . $item->id . '/desasignar') }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Quitar Rol asignados al usuario"><button
                                                                        class="btn btn-danger btn-sm"><i
                                                                            class="fa fa-minus text-white"
                                                                            aria-hidden="true"></i></button></a>
                                                            @else
                                                                <a href="{{ url('/permisos/' . $perid . '/usuarios/' . $item->id . '/desasignar') }}"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Quitar permiso asignados al rol"><button
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
                                {{-- <div class="pagination-wrapper"> {!! $user->appends(['search' => Request::get('search')])->render() !!} </div> --}}
                                @if(!empty($user))
                                    {{ $user->links() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($esabm === false)
                @include('seguridad.usuario.asignar')
            @endif
        </div>
    </div>
@endsection
