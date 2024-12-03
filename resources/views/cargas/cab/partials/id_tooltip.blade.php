<span data-bs-toggle="tooltip" data-bs-placement="top" 
    @if(!empty($item->name))
        data-bs-title="usuario: {{ $item->name }} - cargado: {{ $item->created_at }}"
    @endif
    class="badge bg-primary">
    {{ $item->id }}
</span>