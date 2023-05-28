@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Opciones
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-12">

                @includeif('partials.errors')

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Update') }} Opciones</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('opcion.update', $opciones->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('entidades.opcion.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
