@extends('layouts.main')

@section('contenido')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-12">

                @includeif('partials.errors')

                <div class="card card-default">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="card-title">{{ __('Modificar') }} Prácticas del nomenclador</span>
                        <a href="{{ route('nomenclador.index', $nomenclador->nom_padre_id) }}" title="Volver">
                            <button class="btn btn-warning btn-sm float-right">
                                <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                            </button>
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('nomenclador.store', $nomenclador->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf
                            @include('entidades.nomenclador.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
