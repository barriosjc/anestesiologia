@extends('layouts.main')

@section('contenido')

<div class="container">
    <h1>Resultado de Migraciones y Seeder</h1>

        @if($status === 'success')
            <div class="success">
                @foreach($messages as $message)
                    <p><strong>Éxito:</strong> {{ $message }}</p>
                @endforeach
            </div>
        @else
            <div class="error">
                @foreach($messages as $message)
                    <p><strong>Error:</strong> {{ $message }}</p>
                @endforeach
            </div>
        @endif

        @if(!empty($outputs))
            <div>
                <h3>Salida de los comandos:</h3>
                @foreach($outputs as $output)
                    @if($output)
                        <div class="output">{{ $output }}</div>
                    @endif
                @endforeach
            </div>
        @endif

        <a href="{{ url('/') }}">Volver al inicio</a>
    </div>
@endsection