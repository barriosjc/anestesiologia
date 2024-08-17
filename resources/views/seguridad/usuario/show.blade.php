@extends('layouts.main')

@section('titulo', 'Usuarios')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Usuario {{ $user->id }}</div>
                        <div class="card-body">

                            <a href="{{ url('/usuario') }}" title="Back"><button class="btn btn-warning btn-sm"><i
                                        class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
                            <a href="{{ url('/usuario/' . $user->id . '/edit') }}" title="Edit Usuario"><button
                                    class="btn btn-primary btn-sm"><i class="far fa-edit"></i></button></a>

                            <form method="POST" action="{{ url('usuario' . '/' . $user->id) }}" accept-charset="UTF-8"
                                style="display:inline">
                                {{ method_field('DELETE') }}
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete Usuario"
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
                                            <td>{{ $user->id }}</td>
                                        </tr>
                                        <tr>
                                            <th> Usuario </th>
                                            <td> {{ $user->name }} </td>
                                        </tr>
                                        <tr>
                                            <th> Avatar </th>
                                            <td> <img src="{{ Storage::disk("usuarios")->url($user->foto) }}" class="rounded-circle"
                                                    width="45px" alt=""> </td>
                                        </tr>
                                        <tr>
                                            <th> Usu Verificado </th>
                                            <td> {{ $user->email_verified_at }} </td>
                                        </tr>
                                        <tr>
                                            <th> eMail </th>
                                            <td> {{ $user->email }} </td>
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
