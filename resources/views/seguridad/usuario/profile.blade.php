@extends('layouts.main')

@section('titulo', 'Perfil de usuario')
@section('contenido')
    <div class="container-xl px-4 mt-4">
        <!-- Account page navigation-->
        <nav class="nav nav-borders">
            <a class="nav-link" id="perfil" href="{{ route('profile', ['id' => Auth()->user()->id]) }}">Perfil usuario</a>
            <a class="nav-link" id="password" href="{{ route('profile.password') }}">Cambio de password</a>
            {{-- <a class="nav-link" id="puntos" href="account-security.html">Puntos</a> --}}
        </nav>
        <hr class="mt-0 mb-4" />

        @yield('profield')

    </div>
@endsection
