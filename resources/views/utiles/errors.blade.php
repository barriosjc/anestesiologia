{{-- Errores de validación del componente Livewire en curso.
     Los flashes de sesión (success/error/message) los imprime el layout
     (layouts.main incluye utiles.alerts), así que acá solo van los errores
     del formulario para no mostrarlos duplicados. --}}
@if ($errors->any())
    <div class="alert alert-danger py-2">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif