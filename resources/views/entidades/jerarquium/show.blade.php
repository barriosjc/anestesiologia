@extends('layouts.app')

@section('template_title')
    {{ $jerarquium->name ?? "{{ __('Show') Jerarquium" }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Jerarquium</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary" href="{{ route('jerarquias.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body">
                        
                        <div class="form-group">
                            <strong>Users Id:</strong>
                            {{ $jerarquium->users_id }}
                        </div>
                        <div class="form-group">
                            <strong>Users Empresas Id:</strong>
                            {{ $jerarquium->users_empresas_id }}
                        </div>
                        <div class="form-group">
                            <strong>User Jefe Id:</strong>
                            {{ $jerarquium->jefe_user_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
