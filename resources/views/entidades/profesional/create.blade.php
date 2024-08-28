@extends('layouts.main')

@section('template_title')
    {{ __('Create') }} Profesional
@endsection

@section('contenido')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="card-title">{{ __('Crear') }} Profesional</span>
                        <a href="{{ route('profesionales.index') }}" title="Volver">
                            <button class="btn btn-warning btn-sm float-right">
                                <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                            </button>
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profesionales.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('entidades.profesional.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
