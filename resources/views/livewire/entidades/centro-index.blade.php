<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Centros de atención') }}
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
                                        <th>CUIT</th>
                                        <th>Telefono</th>
                                        <th>Contacto</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($centros as $item)
                                        <tr wire:key="centro-{{ $item->id }}">
                                            <td>{{ $centros->firstItem() + $loop->index }}</td>
                                            <td>{{ $item->nombre }}</td>
                                            <td>{{ $item->cuit }}</td>
                                            <td>{{ $item->telefono }}</td>
                                            <td>{{ $item->contacto }}</td>
                                            <td class="td-actions text-center">
                                                <button type="button" class="btn btn-sm btn-success"
                                                    data-bs-toggle="tooltip" data-bs-title="Editar centro"
                                                    wire:click="abrirModalEditar({{ $item->id }})">
                                                    <i class="fa fa-fw fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    title="Borrar centro"
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
                @if ($centros->hasPages())
                    {{ $centros->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- modal crear/editar centro --}}
    <div class="modal fade" id="centroModal" tabindex="-1" aria-labelledby="centroModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalId ? 'Modificar' : 'Crear' }} centro</h5>
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
                                <label class="small mb-1" for="m_cuit">CUIT</label>
                                <input wire:model="cuit" class="form-control form-control-sm" id="m_cuit"
                                    type="text" placeholder="cuit" />
                                @error('cuit') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="m_telefono">Teléfono</label>
                                <input wire:model="telefono" class="form-control form-control-sm" id="m_telefono"
                                    type="text" placeholder="Ingrese el teléfono" />
                                @error('telefono') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="m_contacto">Contacto</label>
                                <input wire:model="contacto" class="form-control form-control-sm" id="m_contacto"
                                    type="text" placeholder="contacto" />
                                @error('contacto') <span class="text-danger small">{{ $message }}</span> @enderror
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
