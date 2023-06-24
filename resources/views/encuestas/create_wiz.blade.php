@extends('layouts.main')
@include('utiles.alerts')
@section('titulo', 'Alta de encuestas')
@section('contenido')
    @livewire('create-encuesta')
@endsection
