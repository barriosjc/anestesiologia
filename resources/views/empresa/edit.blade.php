@extends('layouts.main')

@section('titulo', 'Empresas')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="">
                <div class="col-md-12">
                    @include('utiles.alerts')
                    <div class="card card-default">
                        <div class="card-header">
                            <a href="{{ route('empresas.index') }}" title="Volver"><button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
                            <span class="card-title">{{ __('Update') }} Empresa</span>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('empresas.update', $empresa->id) }}" role="form"
                                enctype="multipart/form-data">
                                {{ method_field('PATCH') }}
                                @csrf

                                @include('empresa.form')

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
