@extends('layouts.main')

@section('titulo', 'Empresas')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-default">
                        <div class="card-header">
                            <a href="{{ route('empresas.index') }}" title="Volver"><button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
                            <span class="card-title">{{ __('Create') }} Empresa</span>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('empresas.store') }}" role="form"
                                enctype="multipart/form-data">
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
