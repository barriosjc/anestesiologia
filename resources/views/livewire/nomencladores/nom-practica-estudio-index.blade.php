<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">

                            <span id="card_title">
                                Nomenclador de prácticas y estudios ({{ $nomPadre->nombre }})
                            </span>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="input-group input-group-sm">
                                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                                        placeholder="Buscar...">
                                    <button class="btn btn-outline-success" type="button" wire:click="resetPage">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                                <a href="{{ route('nom_padres.index', ['tipo' => $nomPadre->tipo]) }}"
                                    title="Volver" class="btn btn-warning btn-sm float-right">
                                    <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                                </a>
                                <button wire:click="abrirModalNuevo" class="btn btn-primary btn-sm float-right"
                                    data-placement="left">
                                    {{ __('Nuevo') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('utiles.alerts')

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>Id</th>
                                        <th>Padre</th>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nomPracticasEstudios as $item)
                                        <tr wire:key="nom-practica-{{ $item->id }}">
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->nom_padre_id }}</td>
                                            <td>{{ $item->codigo }}</td>
                                            <td>{{ $item->nombre }}</td>
                                            <td>
                                                @if ($item->deleted_at)
                                                    <button type="button" class="btn btn-warning btn-sm "
                                                        title="Volver a poner activa práctica o estudio que esta borrada."
                                                        data-bs-toggle="tooltip"
                                                        onclick="confirmDelete({{ $item->id }}, '¿Desea restaurar este registro?', () => @this.restaurar({{ $item->id }}))">
                                                        <i class="fas fa-undo-alt"></i>
                                                    </button>
                                                @else
                                                    <a class="btn btn-sm btn-primary"
                                                        href="{{ route('nomenclador.valores.filtrar', ['nivel' => $item->codigo]) }}"
                                                        title="Valores"><i class="fa fa-dollar-sign"></i></a>
                                                    <button type="button" class="btn btn-sm btn-success"
                                                        title="Modificar práctica o estudio"
                                                        wire:click="abrirModalEditar({{ $item->id }})">
                                                        <i class="fa fa-fw fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Borrar práctica o estudio"
                                                        onclick="confirmDelete({{ $item->id }}, 'Confirma eliminar la práctica o estudio?', () => @this.borrar({{ $item->id }}))">
                                                        <i class="far fa-trash-alt text-white"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if ($nomPracticasEstudios->hasPages())
                    {{ $nomPracticasEstudios->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- modal crear/editar práctica o estudio --}}
    <div class="modal fade" id="nomPracticaModal" tabindex="-1" aria-labelledby="nomPracticaModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalId ? 'Modificar' : 'Crear' }} práctica o estudio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardar" novalidate>
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-4">
                                <label class="small mb-1" for="pe_codigo">Código</label>
                                <input wire:model="codigo" class="form-control form-control-sm" id="pe_codigo"
                                    type="text" placeholder="codigo" />
                                @error('codigo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="small mb-1" for="pe_nombre">Nombre</label>
                                <input wire:model="nombre" class="form-control form-control-sm" id="pe_nombre"
                                    type="text" placeholder="Ingrese descripción de la práctica" />
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-modal', (event) => {
                new bootstrap.Modal(document.getElementById(event.modal)).show();
            });
            Livewire.on('close-modal', (event) => {
                bootstrap.Modal.getInstance(document.getElementById(event.modal))?.hide();
            });
        });
    </script>
</div>
