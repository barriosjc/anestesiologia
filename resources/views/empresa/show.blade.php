@extends('layouts.app')

@section('template_title')
    {{ $empresa->name ?? "{{ __('Show') Empresa" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Empresa</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('empresas.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="form-group">
                            <strong>Razon Rosial:</strong>
                            {{ $empresa->razon_rosial }}
                        </div>
                        <div class="form-group">
                            <strong>Contacto:</strong>
                            {{ $empresa->contacto }}
                        </div>
                        <div class="form-group">
                            <strong>Telefono:</strong>
                            {{ $empresa->telefono }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
