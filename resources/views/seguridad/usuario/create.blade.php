@extends('layouts.main')

@section('titulo', 'Usuarios')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Crear un nuevo: Usuario</div>
                        <div class="card-body">
                            <a href="{{ url('/usuario') }}" title="Volver"><button class="btn btn-warning btn-sm"><i
                                        class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
                            <br />
                            <br />
                            <form method="POST" action="{{ url('/usuario') }}" accept-charset="UTF-8"
                                class="form-horizontal" enctype="multipart/form-data">
                                {{ csrf_field() }}

                                @include ('seguridad.usuario.form', ['formMode' => 'create'])

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
