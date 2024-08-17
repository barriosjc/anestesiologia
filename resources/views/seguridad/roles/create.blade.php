@extends('layouts.main')

@section('titulo', 'Seguridad')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            Crear un nuevo: Role
                            <a href="{{ url('/roles') }}" title="Volver"><button class="btn btn-warning btn-sm float-right"><i
                                class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ url('/roles') }}" accept-charset="UTF-8" class="form-horizontal"
                                enctype="multipart/form-data">
                                {{ csrf_field() }}

                                @include ('seguridad.roles.form', ['formMode' => 'create'])

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
