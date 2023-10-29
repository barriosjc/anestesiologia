@extends('layouts.main')

@section('titulo', 'Seguridad')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Permiso {{ $permisos->id }}</div>
                        <div class="card-body">

                            <a href="{{ url('/permisos') }}" title="Back"><button class="btn btn-warning btn-sm"><i
                                        class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
                            <a href="{{ url('/permisos/' . $permisos->id . '/edit') }}" title="Edit role"><button
                                    class="btn btn-primary btn-sm"><i class="far fa-edit"></i></button></a>

                            <form method="POST" action="{{ url('permisos' . '/' . $permisos->id) }}" accept-charset="UTF-8"
                                style="display:inline">
                                {{ method_field('DELETE') }}
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete Role"
                                    onclick="return confirm('Confirma eliminar?')"><i
                                        class="far fa-trash-alt text-white"></i></button>
                            </form>
                            <br />
                            <br />

                            <div class="table-responsive">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th>ID</th>
                                            <td>{{ $permisos->id }}</td>
                                        </tr>
                                        <tr>
                                            <th> Nombre </th>
                                            <td> {{ $permisos->name }} </td>
                                        </tr>
                                        <tr>
                                            <th> Guard name </th>
                                            <td> {{ $permisos->guard_name }} </td>
                                        </tr>
                                        <tr>
                                            <th>Fecha Alta </th>
                                            <td> {{ $permisos->created_at }} </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
