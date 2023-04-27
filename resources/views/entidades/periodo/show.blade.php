@extends('layouts.app')

@section('template_title')
    {{ $periodo->name ?? "{{ __('Show') Periodo" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Periodo</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('periodos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="form-group">
                            <strong>Descripcion:</strong>
                            {{ $periodo->descripcion }}
                        </div>
                        <div class="form-group">
                            <strong>Desde:</strong>
                            {{ $periodo->desde }}
                        </div>
                        <div class="form-group">
                            <strong>Hasta:</strong>
                            {{ $periodo->hasta }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
