<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Parametros de atención') }}
                            </span>

                            <div class="float-right">
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
                                        <th>Nro</th>
                                        <th>Nombre</th>
                                        <th>Valor</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($parametros as $item)
                                        <tr wire:key="parametro-{{ $item->id }}">
                                            <td>{{ $parametros->firstItem() + $loop->index }}</td>
                                            <td>{{ $item->nombre }}</td>
                                            <td>{{ $item->valor }}</td>
                                            <td class="td-actions text-center">
                                                <button type="button" class="btn btn-sm btn-success"
                                                    data-bs-toggle="tooltip" data-bs-title="Editar parámetro"
                                                    wire:click="abrirModalEditar({{ $item->id }})">
                                                    <i class="fa fa-fw fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    title="Borrar parámetro"
                                                    onclick="confirmDelete({{ $item->id }}, 'Esta acción es irreversible.', () => @this.borrar({{ $item->id }}))">
                                                    <i class="far fa-trash-alt text-white"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if ($parametros->hasPages())
                    {{ $parametros->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- modal crear/editar parámetro --}}
    <div class="modal fade" id="parametroModal" tabindex="-1" aria-labelledby="parametroModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalId ? 'Modificar' : 'Crear' }} parámetro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardar" novalidate>
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="m_nombre">Nombre</label>
                                <input wire:model="nombre" class="form-control form-control-sm" id="m_nombre"
                                    type="text" placeholder="Ingrese su nombre y apellido" />
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="m_valor">Valor</label>
                                <input wire:model="valor" class="form-control form-control-sm" id="m_valor"
                                    type="text" placeholder="Ingrese un valor" />
                                @error('valor') <span class="text-danger small">{{ $message }}</span> @enderror
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
