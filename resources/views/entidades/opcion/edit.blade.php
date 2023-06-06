@extends('layouts.main')

@section('titulo', 'Opiciones')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                @include('utiles.alerts')
                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Update') }} Opciones</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('opcion.update', $opciones->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('entidades.opcion.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
