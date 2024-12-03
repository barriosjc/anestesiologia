@php
    // Determinar el color de la etiqueta según el valor de est_id
    $badgeColor = match ($item->est_id) {
        1 => 'primary',
        2 => 'danger',
        3 => 'warning',
        4 => 'info',
        5 => 'secondary',
        default => 'success',
    };
@endphp

<span data-bs-toggle="tooltip" data-bs-placement="top" 
    @if(!empty($item->observacion))
        data-bs-title="{{ $item->observacion }}"
    @endif
    class="badge bg-{{ $badgeColor }}">
    {{ $item->est_descripcion }}
    
    @if(!empty($item->observacion))
        <span class="badge text-bg-dark"></span>
    @endif
</span>
