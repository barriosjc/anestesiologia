<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <span id="card_title">
                                Equivalencias UOM → Nivel
                            </span>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="input-group input-group-sm">
                                    <input type="text" wire:model.live.debounce.300ms="text"
                                        class="form-control" placeholder="Buscar...">
                                    <span class="input-group-text">
                                        <i class="fa fa-search"></i>
                                    </span>
                                </div>

                                <button wire:click="abrirModalNuevo" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-plus me-1"></i>Nuevo
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
                                        <th>Código UOM</th>
                                        <th>Nivel interno</th>
                                        <th>Descripción</th>
                                        <th style="width: 12%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($niveles as $item)
                                        <tr wire:key="nivel-{{ $item->id }}">
                                            <td class="fw-semibold">{{ $item->cod_uom }}</td>
                                            <td>{{ $item->nivel }}</td>
                                            <td>{{ $item->descripcion ?? '—' }}</td>
                                            <td class="td-actions text-center">
                                                <button type="button" class="btn btn-sm btn-success"
                                                    data-bs-toggle="tooltip" data-bs-title="Editar equivalencia"
                                                    wire:click="abrirModalEditar({{ $item->id }})">
                                                    <i class="fa fa-fw fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    title="Borrar equivalencia"
                                                    onclick="confirmDelete({{ $item->id }}, 'Esta acción es irreversible.', () => @this.borrar({{ $item->id }}))">
                                                    <i class="far fa-trash-alt text-white"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                No hay equivalencias cargadas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($niveles->hasPages())
                        <div class="card-footer py-2">
                            {{ $niveles->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="nivelModal" tabindex="-1" aria-labelledby="nivelModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalId ? 'Modificar' : 'Crear' }} equivalencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardar" novalidate>
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="m_cod_uom">Código UOM (archivo)</label>
                                <input wire:model="cod_uom" class="form-control form-control-sm" id="m_cod_uom"
                                    type="text" placeholder="Ej: 600011" />
                                @error('cod_uom') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="m_nivel_interno">Nivel interno</label>
                                <input wire:model="nivel" class="form-control form-control-sm" id="m_nivel_interno"
                                    type="text" placeholder="Ej: 600011" />
                                @error('nivel') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small mb-1" for="m_nivel_descripcion">Descripción</label>
                            <input wire:model="descripcion" class="form-control form-control-sm"
                                id="m_nivel_descripcion" type="text" placeholder="Descripción del código" />
                            @error('descripcion') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-1"></i>Cerrar
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                        </button>
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
