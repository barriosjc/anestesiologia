<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Profesionales') }}
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
                                        <th>Email</th>
                                        <th>DNI</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($profesionales as $item)
                                        <tr wire:key="profesional-{{ $item->id }}">
                                            <td>{{ $profesionales->firstItem() + $loop->index }}</td>
                                            <td>{{ $item->nombre }}</td>
                                            <td>{{ $item->email }}</td>
                                            <td>{{ $item->dni }}</td>
                                            <td class="td-actions text-center">
                                                <a class="btn btn-sm btn-primary"
                                                    href="{{ route('profesional.cargar.documentacion', $item->id) }}"
                                                    data-bs-toggle="tooltip" data-bs-title="Documentación">
                                                    <i class="fa fa-file-alt"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-success"
                                                    data-bs-toggle="tooltip" data-bs-title="Editar médico"
                                                    wire:click="abrirModalEditar({{ $item->id }})">
                                                    <i class="fa fa-fw fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    title="Borrar médico"
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
                @if ($profesionales->hasPages())
                    {{ $profesionales->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- modal crear/editar profesional --}}
    <div class="modal fade" id="profesionalModal" tabindex="-1" aria-labelledby="profesionalModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalId ? 'Modificar' : 'Crear' }} profesional</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardar" novalidate>
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="m_nombre">Nombre y apellido</label>
                                <input wire:model="nombre" class="form-control form-control-sm" id="m_nombre"
                                    type="text" placeholder="Ingrese su nombre y apellido" />
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="m_dni">DNI</label>
                                <input wire:model="dni" class="form-control form-control-sm" id="m_dni"
                                    type="text" placeholder="dni" />
                                @error('dni') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small mb-1" for="m_email">Email</label>
                            <input wire:model="email" class="form-control form-control-sm" id="m_email"
                                type="email" placeholder="Ingrese su email" />
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="m_telefono">Teléfono</label>
                                <input wire:model="telefono" class="form-control form-control-sm" id="m_telefono"
                                    type="text" placeholder="telefono" />
                                @error('telefono') <span class="text-danger small">{{ $message }}</span> @enderror
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
