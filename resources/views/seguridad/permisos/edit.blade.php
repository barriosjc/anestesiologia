@extends('layouts.main')

@section('titulo', 'Seguridad')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            Editar permiso #{{ $permisos->id }}
                            <a href="{{ url('/permisos') }}" class="btn btn-warning btn-sm float-right"><i
                                class="fa fa-arrow-left" aria-hidden="true"></i> Volver</a>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ url('/permisos/' . $permisos->id) }}" accept-charset="UTF-8"
                                class="form-horizontal" enctype="multipart/form-data">
                                {{ method_field('PATCH') }}
                                {{ csrf_field() }}

                                @include ('seguridad.permisos.form', ['formMode' => 'edit'])

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
