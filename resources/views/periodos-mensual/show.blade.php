@extends('layouts.app')

@section('template_title')
    {{ $periodosMensual->name ?? "{{ __('Show') Periodos Mensual" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Periodos Mensual</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('periodosmensual.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="form-group">
                            <strong>Descripcion:</strong>
                            {{ $periodosMensual->descripcion }}
                        </div>
                        <div class="form-group">
                            <strong>Desde:</strong>
                            {{ $periodosMensual->desde }}
                        </div>
                        <div class="form-group">
                            <strong>Hasta:</strong>
                            {{ $periodosMensual->hasta }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
