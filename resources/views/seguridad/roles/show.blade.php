@extends('layouts.main')

@section('titulo', 'Seguridad')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Role {{ $roles->id }}</div>
                        <div class="card-body">

                            <a href="{{ url('/roles') }}" title="Back"><button class="btn btn-warning btn-sm"><i
                                        class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
                            <a href="{{ url('/roles/' . $roles->id . '/edit') }}" title="Edit role"><button
                                    class="btn btn-primary btn-sm"><i class="far fa-edit"></i></button></a>

                            <form method="POST" action="{{ url('roles' . '/' . $roles->id) }}" accept-charset="UTF-8"
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
                                            <td>{{ $roles->id }}</td>
                                        </tr>
                                        <tr>
                                            <th> Nombre </th>
                                            <td> {{ $roles->name }} </td>
                                        </tr>
                                        <tr>
                                            <th> Guard name </th>
                                            <td> {{ $roles->guard_name }} </td>
                                        </tr>
                                        <tr>
                                            <th>Fecha Alta </th>
                                            <td> {{ $roles->created_at }} </td>
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
