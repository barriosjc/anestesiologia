@php
    $puntoTitulo = $titulo ?? 'El dato del archivo no coincide con el del sistema';
@endphp
<span class="opa-dif-punto" title="{{ $puntoTitulo }}" aria-hidden="true"></span>
