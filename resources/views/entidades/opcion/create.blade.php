@extends('layouts.main')

@section('titulo', 'Opiciones')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Nueva') }} Opción</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('opcion.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('entidades.opcion.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
