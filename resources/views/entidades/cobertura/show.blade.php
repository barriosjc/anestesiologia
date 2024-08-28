@extends('layouts.main')

@section('titulo', 'Usuarios')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Cobertura {{ $cobertura->id }}</div>
                        <div class="card-body">

                            <a href="{{ route('coberturas.index') }}" title="Back"><button class="btn btn-warning btn-sm"><i
                                        class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
                            <a href="{{ route('coberturas.update', $cobertura->id) }}" title="Edit Usuario"><button
                                    class="btn btn-primary btn-sm"><i class="far fa-edit"></i></button></a>

                            <form method="POST" action="{{ route('coberturas.delete', $cemtro->id) }}" accept-charset="UTF-8"
                                style="display:inline">
                                {{ method_field('DELETE') }}
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete Usuario"
                                        onclick="confirmDelete({{ $cobertura->id }})"><i class="far fa-trash-alt text-white"></i>
                                </button>
                            </form>
                            <br />
                            <br />

                            <div class="table-responsive">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th>ID</th>
                                            <td>{{ $cobertura->id }}</td>
                                        </tr>
                                        <tr>
                                            <th> Usuario </th>
                                            <td> {{ $cobertura->name }} </td>
                                        </tr>
                                        <tr>
                                            <th> Avatar </th>
                                            <td> <img src="{{ Storage::disk("usuarios")->url($cobertura->foto) }}" class="rounded-circle"
                                                    width="45px" alt=""> </td>
                                        </tr>
                                        <tr>
                                            <th> Usu Verificado </th>
                                            <td> {{ $cobertura->email_verified_at }} </td>
                                        </tr>
                                        <tr>
                                            <th> eMail </th>
                                            <td> {{ $cobertura->email }} </td>
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
