@extends('layouts.main')

@section('contenido')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="card-title">{{ __('Crear') }} Listas de precios</span>
                        <a href="{{ route('nomenclador.listas.listas') }}" title="Volver">
                            <button class="btn btn-warning btn-sm float-right">
                                <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                            </button>
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('nomenclador.lista.guardar') }}"  role="form" enctype="multipart/form-data">
                            @csrf
                            @include('entidades.nomenclador.form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
