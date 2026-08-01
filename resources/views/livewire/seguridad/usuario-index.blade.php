<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                Usuarios
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

                        <div class="form-inline my-2 my-lg-0 float-right">
                            <div class="input-group">
                                <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                                    placeholder="Buscar...">
                                <span class="input-group-append">
                                    <button class="btn btn-info" type="button" wire:click="resetPage">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>#</th>
                                        <th>Usuario</th>
                                        <th>Avatar</th>
                                        <th>Mail</th>
                                        <th class="float-right" style="width:25%">Valores</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $item)
                                        <tr wire:key="usuario-{{ $item->id }}">
                                            <td>{{ $users->firstItem() + $loop->index }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td><img src="{{ Storage::disk('usuarios')->url($item->foto) }}"
                                                    class="rounded-circle" width="45px" alt=""></td>
                                            <td>{{ $item->email }}</td>
                                            <td>
                                                <div class="float-right">
                                                    <div class="btn-group btn-group-sm" role="group"
                                                        aria-label="Basic example">
                                                        <a href="{{ url('/usuario/' . $item->id . '/roles') }}"><button
                                                                class="btn btn-warning btn-sm" data-bs-toggle="tooltip"
                                                                title="Roles asignados al usuario."><i
                                                                    class="fa fa-users" aria-hidden="true"></i></button></a>
                                                        <a href="{{ url('/usuario/' . $item->id . '/permisos') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="Permisos asignados al usuario "><button
                                                                class="btn btn-success btn-sm"><i class="fa fa-key"
                                                                    aria-hidden="true"></i></button></a>
                                                        <button type="button" class="btn btn-primary btn-sm"
                                                            data-bs-toggle="tooltip" title="Editar Usuario"
                                                            wire:click="abrirModalEditar({{ $item->id }})">
                                                            <i class="far fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            data-bs-toggle="tooltip" title="Borrar Usuario"
                                                            onclick="confirmDelete({{ $item->id }}, 'Confirma eliminar el usuario?', () => @this.borrar({{ $item->id }}))">
                                                            <i class="far fa-trash-alt text-white"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if ($users->hasPages())
                    {{ $users->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- modal crear/editar usuario --}}
    <div class="modal fade" id="usuarioModal" tabindex="-1" aria-labelledby="usuarioModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalId ? 'Modificar' : 'Crear' }} usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardar" novalidate>
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="u_name">Nombre y apellido</label>
                                <input wire:model="name" class="form-control form-control-sm" id="u_name" type="text"
                                    placeholder="Ingrese nombre y apellido" />
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="u_email">Email</label>
                                <input wire:model="email" class="form-control form-control-sm" id="u_email"
                                    type="email" placeholder="Ingrese el email" />
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="u_centro">Centro</label>
                                <select wire:model="centro_id" class="form-select form-select-sm" id="u_centro">
                                    <option value="">Seleccione...</option>
                                    @foreach ($centros as $centro)
                                        <option value="{{ $centro->id }}">{{ $centro->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('centro_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Perfiles</label>
                                <div class="border rounded p-2" style="max-height: 120px; overflow-y: auto;">
                                    @foreach ($roles as $role)
                                        <div class="form-check" wire:key="perfil-{{ $role->id }}">
                                            <input class="form-check-input" type="checkbox" wire:model="perfiles"
                                                value="{{ $role->id }}" id="perfil-{{ $role->id }}">
                                            <label class="form-check-label small" for="perfil-{{ $role->id }}">
                                                {{ $role->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('perfiles') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if ($modalId)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="blanquear" id="u_blanquear">
                                <label class="form-check-label small" for="u_blanquear">
                                    Blanquear contraseña (restablece a 12345678)
                                </label>
                            </div>
                        @endif
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
