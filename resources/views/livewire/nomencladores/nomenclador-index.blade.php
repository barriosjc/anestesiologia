<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span id="card_title">
                                Nomenclador de anestesiología ({{ $nomPadre->nombre }})
                            </span>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="input-group input-group-sm">
                                    <input type="text" wire:model="text" wire:keydown.enter="filtrar"
                                        class="form-control" placeholder="Buscar...">
                                    <button type="button" wire:click="filtrar" class="btn btn-outline-success">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>

                                <a href="{{ route('nom_padres.index', ['tipo' => $nomPadre->tipo]) }}"
                                    class="btn btn-warning btn-sm">
                                    <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                                </a>

                                <button wire:click="abrirModalNuevo" class="btn btn-primary btn-sm">
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
                                        <th>Padre</th>
                                        <th>Código</th>
                                        <th>Nivel</th>
                                        <th>Nombre</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nomenclador as $item)
                                        <tr wire:key="nomenclador-{{ $item->id }}">
                                            <td>{{ $item->nom_padre_id }}</td>
                                            <td>{{ $item->codigo }}</td>
                                            <td>{{ $item->nivel }}</td>
                                            <td>{{ $item->descripcion }}</td>
                                            <td class="td-actions text-center">
                                                @if (empty($item->deleted_at))
                                                    <a class="btn btn-sm btn-primary"
                                                        href="{{ route('nomenclador.valores.filtrar', ['nivel' => $item->codigo]) }}"
                                                        data-bs-toggle="tooltip" data-bs-title="Ver valores">
                                                        <i class="fa fa-dollar-sign"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-success"
                                                        data-bs-toggle="tooltip" data-bs-title="Editar práctica"
                                                        wire:click="abrirModalEditar({{ $item->id }})">
                                                        <i class="fa fa-fw fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Borrar práctica"
                                                        onclick="confirmDelete({{ $item->id }}, 'Esta acción es irreversible.', () => @this.borrar({{ $item->id }}))">
                                                        <i class="far fa-trash-alt text-white"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-warning btn-sm"
                                                        title="Volver a poner activa la práctica que esta borrada."
                                                        data-bs-toggle="tooltip"
                                                        onclick="confirmJob('¿Desea restaurar este registro?', null, false, '', () => @this.borrar({{ $item->id }}))">
                                                        <i class="fas fa-undo-alt"></i>
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
                @if ($nomenclador->hasPages())
                    {{ $nomenclador->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- modal crear/editar nomenclador --}}
    <div class="modal fade" id="nomencladorModal" tabindex="-1" aria-labelledby="nomencladorModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalId ? 'Modificar' : 'Crear' }} práctica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardar" novalidate>
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-4">
                                <label class="small mb-1" for="m_codigo">Código</label>
                                <input wire:model="codigo" class="form-control form-control-sm" id="m_codigo"
                                    type="text" placeholder="codigo" />
                                @error('codigo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1" for="m_nivel">Nivel</label>
                                <input wire:model="nivel" class="form-control form-control-sm" id="m_nivel"
                                    type="text" placeholder="nivel" />
                                @error('nivel') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small mb-1" for="m_descripcion">Descripción</label>
                            <input wire:model="descripcion" class="form-control form-control-sm" id="m_descripcion"
                                type="text" placeholder="Ingrese descripción de la práctica" />
                            @error('descripcion') <span class="text-danger small">{{ $message }}</span> @enderror
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
