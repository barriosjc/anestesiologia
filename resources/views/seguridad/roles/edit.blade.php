@extends('layouts.main')

@section('titulo', 'Seguridad')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Editar Rol #{{ $roles->id }}</div>
                        <div class="card-body">
                            <a href="{{ url('/roles') }}" title="Volver"><button class="btn btn-warning btn-sm"><i
                                        class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
                            <br />
                            <br />
                            <form method="POST" action="{{ url('/roles/' . $roles->id) }}" accept-charset="UTF-8"
                                class="form-horizontal" enctype="multipart/form-data">
                                {{ method_field('PATCH') }}
                                {{ csrf_field() }}

                                @include ('seguridad.roles.form', ['formMode' => 'edit'])

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
