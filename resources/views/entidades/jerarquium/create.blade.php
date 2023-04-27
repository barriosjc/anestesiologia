@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} Jerarquium
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">

                @includeif('partials.errors')
                @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
        <div class="alert alert-danger">
            <p>{{ $message }}</p>
        </div>

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Create') }} Jerarquium</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('jerarquias.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('entidades.jerarquium.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
