@extends('layouts.main')

@section('titulo', 'Opiciones')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Ver') }} Opciones</span>
                        </div>
                        <a href="{{ URL::previous() }}" title="Volver"><button class="btn btn-warning btn-sm"><i
                            class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
