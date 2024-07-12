@extends('layouts.main')

@section('contenido')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="card-title">{{ $parte_id == null ? "Crear" : "Modificar" }} Parte</span>
                        <div>
                            <a href="{{ route('partes_cab.index') }}" class="btn btn-info btn-sm"  data-placement="left">
                              Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('partes_cab.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('cargas.cab.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
