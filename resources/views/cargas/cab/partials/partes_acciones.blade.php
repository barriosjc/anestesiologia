<a class="btn btn-sm btn-info llama_modal" data-bs-toggle="modal" 
    data-bs-target="#valorModal" 
    data-id="{{ $item->id}}"
    data-observaciones="{{ $item->observacion }}"
    data-bs-toggle="tooltip" data-bs-placement="top" 
    data-bs-title="Cargado todo el parte, ahora para pasar: A liquidar, click aquí.">
    <i class="fa-solid fa-rotate-right"></i>
</a>
<a class="btn btn-sm btn-success" href="{{ route('partes_cab.edit', $item->id) }}">
    <i class="fa fa-fw fa-edit"></i>
</a>
<form id="delete-form-{{ $item->id }}" action="{{ route('partes_cab.destroy', $item->id) }}" method="POST">
    @csrf
    @method('DELETE')
    <button type="button" class="btn btn-danger btn-sm"
        title="Delete parte"
        onclick="confirmDelete({{ $item->id }})">
        <i class="far fa-trash-alt text-white"></i>
    </button>
</form>
