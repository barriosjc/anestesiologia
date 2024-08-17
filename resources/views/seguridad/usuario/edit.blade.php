@extends('layouts.main')

@section('titulo', 'Usuarios')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            Editar Usuario #{{ $user->id }}
                            <a href="{{ url('/usuario') }}" title="Volver"><button class="btn btn-warning btn-sm"><i
                                class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ url('/usuario/' . $user->id) }}" accept-charset="UTF-8"
                                class="form-horizontal" enctype="multipart/form-data">
                                {{ method_field('PATCH') }}
                                {{ csrf_field() }}

                                @include ('seguridad.usuario.form', ['formMode' => 'edit'])

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
