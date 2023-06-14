@extends('layouts.main')

@section('titulo', 'Empresas')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="float-left">
                                <a href="{{ route('empresas.index') }}" title="Volver"><button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
                                <span class="card-title">{{ __('Show') }} Empresa</span>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="form-group">
                                <strong>Razón Social:</strong>
                                {{ $empresa->razon_social }}
                            </div>
                            <div class="form-group">
                                <strong>Contacto:</strong>
                                {{ $empresa->contacto }}
                            </div>
                            <div class="form-group">
                                <strong>Teléfono:</strong>
                                {{ $empresa->telefono }}
                            </div>
                            <div class="form-group">
                                <strong>Prefijo:</strong>
                                {{ $empresa->uri }}
                            </div>
                            <div class="form-group">
                                <strong>Logo:</strong>
                                {{ $empresa->logo }}
                            </div>
                            <div class="form-group">
                                <strong>email de mailing:</strong>
                                {{ $empresa->email_contacto }}
                            </div>
                            <div class="form-group">
                                <strong>nombre de contacto mailing:</strong>
                                {{ $empresa->email_nombre }}
                            </div>
                            <div class="form-group">
                                <strong>Perfiles:</strong>
                                @php($sepa = "")
                                @foreach($roles_empresas as $roles)
                                    {{ $sepa . $roles->name }}
                                    @php($sepa = ", ") 
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
