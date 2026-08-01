<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                Roles
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
                                        <th>Nombre</th>
                                        <th>Guard name</th>
                                        <th>Fecha Alta</th>
                                        <th class="float-right" style="width:25%">Valores</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $item)
                                        <tr wire:key="role-{{ $item->id }}">
                                            <td>{{ $roles->firstItem() + $loop->index }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->guard_name }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="float-right">
                                                    <div class="btn-group btn-group-sm" role="group"
                                                        aria-label="Basic example">
                                                        <a href="{{ url('/roles/' . $item->id . '/usuarios') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="Usuarios asignados al grupo"><button
                                                                class="btn btn-warning btn-sm"><i class="fa fa-users"
                                                                    aria-hidden="true"></i></button></a>
                                                        <a href="{{ url('/roles/' . $item->id . '/permisos') }}"
                                                            data-bs-toggle="tooltip"
                                                            title="Permisos asignados al grupo"><button
                                                                class="btn btn-success btn-sm"><i class="fa fa-key"
                                                                    aria-hidden="true"></i></button></a>
                                                        <button type="button" class="btn btn-primary btn-sm"
                                                            data-bs-toggle="tooltip" title="Editar role"
                                                            wire:click="abrirModalEditar({{ $item->id }})">
                                                            <i class="far fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            data-bs-toggle="tooltip" title="Borrar role"
                                                            onclick="confirmDelete({{ $item->id }}, 'Confirma eliminar el role?', () => @this.borrar({{ $item->id }}))">
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
                @if ($roles->hasPages())
                    {{ $roles->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- modal crear/editar role --}}
    <div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalId ? 'Modificar' : 'Crear' }} role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardar" novalidate>
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-12">
                                <label class="small mb-1" for="r_name">Nombre</label>
                                <input wire:model="name" class="form-control form-control-sm" id="r_name" type="text"
                                    placeholder="Ingrese el nombre del role" />
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-12">
                                <label class="small mb-1" for="r_guard">Guard name</label>
                                <input wire:model="guard_name" class="form-control form-control-sm" id="r_guard"
                                    type="text" placeholder="web" />
                                @error('guard_name') <span class="text-danger small">{{ $message }}</span> @enderror
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
