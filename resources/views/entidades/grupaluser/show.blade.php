@extends('layouts.app')

@section('template_title')
    {{ $grupalUser->name ?? "{{ __('Show') Grupal User" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Grupal User</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('grupalusers.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="form-group">
                            <strong>Grupal Id:</strong>
                            {{ $grupalUser->grupal_id }}
                        </div>
                        <div class="form-group">
                            <strong>Users Id:</strong>
                            {{ $grupalUser->users_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
