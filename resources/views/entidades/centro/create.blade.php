@extends('layouts.main')

@section('template_title')
    {{ __('Create') }} Médico
@endsection

@section('contenido')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Create') }} Médico</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route(' centros.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('entidades. centro.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection