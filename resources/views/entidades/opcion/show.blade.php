@extends('layouts.main')

@section('titulo', 'Opiciones')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Opcione</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('opcion.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="form-group">
                            <strong>Descripcion:</strong>
                            {{ $opciones->descripcion }}
                        </div>
                        <div class="form-group">
                            <strong>Detalle:</strong>
                            {{ $opciones->detalle }}
                        </div>
                        <div class="form-group">
                            <strong>Imagen:</strong>
                            {{ $opciones->imagen }}
                        </div>
                        <div class="form-group">
                            <strong>Style:</strong>
                            {{ $opciones->style }}
                        </div>
                        <div class="form-group">
                            <strong>Habilitada:</strong>
                            {{ $opciones->habilitada }}
                        </div>
                        <div class="form-group">
                            <strong>Puntos:</strong>
                            {{ $opciones->puntos }}
                        </div>
                        <div class="form-group">
                            <strong>Puntos Min:</strong>
                            {{ $opciones->puntos_min }}
                        </div>
                        <div class="form-group">
                            <strong>Puntos Max:</strong>
                            {{ $opciones->puntos_max }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
