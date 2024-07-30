@extends('layouts.app')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>SAADA</title>
        {{-- <link href="css/styles.css" rel="stylesheet" /> --}}
        {{-- <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" /> --}}
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js"
            crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous">
        </script>
    </head>

    <body class="bg-primary">
        {{-- style="background-image: url({{ asset(Storage::disk('empresas')->url(session('empresa')->login ?? '')) }}); background-size: cover; background-repeat: no-repeat;"> --}}
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container-xl px-4">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                {{-- Basic login form --}}
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header justify-content-center">
                                        <div class="row">
                                            <div class="col-lg-6 text-start">
                                                <h3 class="fw-light my-4">Login</h3>
                                                <h5 class="fw-light my-4">¡Te damos la bienvenida!</h5>
                                            </div>
                                            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                                                    <img class="dropdown-user-img" style="height:51px;width:110px"
                                                        src="{{  asset("img\logo_grande.jpg") }}" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        {{-- Login form --}}
                                        {{-- <form> --}}
                                        <form method="POST" action="{{ route('login') }}">
                                            @csrf
                                            {{-- Form Group (email address) --}}
                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputEmailAddress">Email</label>
                                                {{-- <input class="form-control" id="inputEmailAddress" type="email" placeholder="Enter email address" /> --}}
                                                <input id="email" type="email"
                                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                                    value="{{ old('email') }}" placeholder="Enter email address" required
                                                    autocomplete="email" autofocus>
                                                @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            {{-- Form Group (password) --}}
                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputPassword">Password</label>
                                                {{-- <input class="form-control" id="inputPassword" type="password" placeholder="Enter password" /> --}}
                                                <input id="password" type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    name="password" placeholder="Enter password" required
                                                    autocomplete="current-password">
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            {{-- Form Group (login box) --}}
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a class="small" href="{{ route('login.restablecer') }}">Olvido su
                                                    password?</a>
                                                {{-- <a class="btn btn-primary" type="submit">Login</a> --}}
                                                <button type="submit" class="btn btn-primary">
                                                    {{ __('Login') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    {{-- <div class="card-footer text-center">
                                    <div class="small"><a href="auth-register-basic.html">Need an account? Sign up!</a></div>
                                </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                {{-- <footer class="footer-admin mt-auto footer-dark">
                    <div class="container-xl px-4">
                        <div class="row">
                            <div class="col-md-6 small">Copyright &copy; Webmedia 2023</div>
                            <div class="col-md-6 text-md-end small">
                                <a href="#!">Privacy Policy</a>
                                &middot;
                                <a href="#!">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer> --}}
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
        </script>
        {{-- <script src="js/scripts.js"></script> --}}
    </body>

    </html>
@endsection
