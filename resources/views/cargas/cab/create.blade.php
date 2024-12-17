@extends('layouts.main')

@section('contenido')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        @php($parte_id = $parte_id ?? session('p_parte_id'))
                        <span class="card-title">{{ $parte_id == null ? "Crear" : "Modificar" }} Parte {{ $parte_id == null ? "" : ", Nro: {$parte_id}" }}</span>
                        <div>
                            <a href="{{ route('partes_cab.filtrar') }}" class="btn btn-info btn-sm"  data-placement="left">
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
