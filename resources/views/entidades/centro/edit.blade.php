@extends('layouts.main')

@section('template_title')
    {{ __('Actualizar ') }} Médico
@endsection

@section('contenido')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-12">

                @includeif('partials.errors')

                <div class="card card-default">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="card-title">{{ __('Modificar') }} Centro</span>
                        <a href="{{ route('centros.index') }}" title="Volver">
                            <button class="btn btn-warning btn-sm float-right">
                                <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                            </button>
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('centros.update', $centros->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('entidades.centro.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
