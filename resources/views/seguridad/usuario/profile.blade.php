@extends('layouts.main')

@section('titulo', 'Usuarios')
@section('contenido')
    <div class="container-fluid">
        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800">{{ __('Profile') }}</h1>

        @if ($errors->any())
            <div class="alert alert-danger border-left-danger" role="alert">
                <ul class="pl-4 my-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-4 order-lg-2">
                <div class="card shadow mb-4">
                    <div class="card-profile-image mt-4">
                        @if (empty(Auth::user()->foto))
                            <img src="/img/usuario.png" class="rounded-circle" alt="user-image" width="200px">
                        @else
                            <img src="{{ asset('storage') . str_replace('public', '', Auth::user()->foto) }} "
                                rounded-circle alt="user-image" width="200px">
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="text-center">
                                    <h5 class="font-weight-bold">{{ Auth::user()->fullName }}</h5>
                                    <p>{{ Auth::user()->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <form method="POST" action="{{ route('profile.update.foto') }}" accept-charset="UTF-8"
                                    class="form-horizontal" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="form-group row ">
                                        <input class="form-control form-control-sm col-9" name="file" type="file"
                                            id="file" />
                                        <input type="submit" />
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="card-profile-stats">
                                    <span class="heading">22</span>
                                    <span class="description">Friends</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card-profile-stats">
                                    <span class="heading">10</span>
                                    <span class="description">Photos</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card-profile-stats">
                                    <span class="heading">89</span>
                                    <span class="description">Comments</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-8 order-lg-1">

                <div class="card shadow mb-4">

                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('My Account') }}</h6>
                    </div>

                    <div class="card-body">

                        <form method="POST" action="{{ route('profile.update') }}" autocomplete="off">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <input type="hidden" name="_method" value="PUT">
                            <div class="pl-lg-4">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group focused">
                                            <label class="form-control-label" for="name">{{ __('Name') }}<span
                                                    class="small text-danger">*</span></label>
                                            <input type="text" id="name" class="form-control" name="name"
                                                placeholder="Name" value="{{ old('name', Auth::user()->name) }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group focused">
                                            <label class="form-control-label" for="last_name">{{ __('Last name') }}</label>
                                            <input type="text" id="last_name" class="form-control" name="last_name"
                                                placeholder="Last name"
                                                value="{{ old('last_name', Auth::user()->last_name) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label class="form-control-label" for="email">Email<span
                                                    class="small text-danger">*</span></label>
                                            <input type="email" id="email" class="form-control" name="email"
                                                placeholder="example@example.com"
                                                value="{{ old('email', Auth::user()->email) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group focused">
                                            <label class="form-control-label" for="current_password">Password
                                                actual</label>
                                            <input type="password" id="current_password" class="form-control"
                                                name="current_password" placeholder="Current password">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group focused">
                                            <label class="form-control-label" for="new_password">Nueva password</label>
                                            <input type="password" id="new_password" class="form-control"
                                                name="new_password" placeholder="New password">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group focused">
                                            <label class="form-control-label" for="confirm_password">Confirme
                                                password</label>
                                            <input type="password" id="confirm_password" class="form-control"
                                                name="password_confirmation" placeholder="Confirm password">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Button -->
                            <div class="pl-lg-4">
                                <div class="row">
                                    <div class="col text-center">
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
