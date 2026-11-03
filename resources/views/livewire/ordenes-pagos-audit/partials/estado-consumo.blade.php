@php
    $consumoEstadoId = (int) ($detalle->consumoDet?->estado_id ?? 0);
    $consumoEstadoNombre = $consumoEstadoId > 0 ? ($estadosParteNombres[$consumoEstadoId] ?? '—') : '—';
    $consumoEstadoBadge = match ($consumoEstadoId) {
        4 => 'bg-primary',
        5 => 'bg-success',
        6 => 'bg-warning text-dark',
        7 => 'bg-info text-dark',
        8 => 'bg-secondary',
        default => 'bg-danger',
    };
@endphp

@if ($detalle->consumoDet)
    <button type="button" class="badge rounded-pill {{ $consumoEstadoBadge }} border-0"
        data-bs-toggle="modal" data-bs-target="#valorModal"
        wire:click="abrirEstadoModal({{ $detalle->id }})"
        title="Cambiar el estado del consumo">
        {{ $consumoEstadoNombre }}
    </button>
@else
    <span class="badge rounded-pill bg-light text-muted border">Sin consumo</span>
@endif
