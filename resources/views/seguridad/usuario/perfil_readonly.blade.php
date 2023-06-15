@extends('layouts.main')

@section('titulo', 'Perfil de usuario')

@section('contenido')
    <div class="container" id="formreadonly">
        @include('seguridad.usuario.perfil_data')
    </div>
@endsection
