<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="card_title">
                                {{ __('Coberturas') }}
                            </span>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="input-group input-group-sm">
                                    <input type="text" wire:model="text" wire:keydown.enter="$refresh"
                                        class="form-control" placeholder="Buscar...">
                                    <button type="button" class="btn btn-outline-success"
                                        wire:click="$refresh">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
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
                                        <th>Sigla</th>
                                        <th>CUIT</th>
                                        <th>Edad desde</th>
                                        <th>Edad hasta</th>
                                        <th>% adic.</th>
                                        <th style="width: 15%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($coberturas as $item)
                                        <tr wire:key="cobertura-{{ $item->id }}">
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->nombre }}</td>
                                            <td>{{ $item->sigla }}</td>
                                            <td>{{ $item->cuit }}</td>
                                            <td>{{ $item->edad_desde }}</td>
                                            <td>{{ $item->edad_hasta }}</td>
                                            <td>{{ $item->porcentaje_adic }}</td>
                                            <td class="td-actions text-center">
                                                <button type="button" class="btn btn-sm btn-success"
                                                    data-bs-toggle="tooltip" data-bs-title="Editar cobertura"
                                                    wire:click="abrirModalEditar({{ $item->id }})">
                                                    <i class="fa fa-fw fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    title="Borrar cobertura"
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
            </div>
        </div>
    </div>

    {{-- modal crear/editar cobertura --}}
    <div class="modal fade" id="coberturaModal" tabindex="-1" aria-labelledby="coberturaModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalId ? 'Modificar' : 'Crear' }} cobertura</h5>
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
                            <div class="col-md-3">
                                <label class="small mb-1" for="m_sigla">Sigla</label>
                                <input wire:model="sigla" class="form-control form-control-sm" id="m_sigla"
                                    type="text" placeholder="Ingrese su sigla" />
                                @error('sigla') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1" for="m_cuit">CUIT</label>
                                <input wire:model="cuit" class="form-control form-control-sm" id="m_cuit"
                                    type="text" placeholder="cuit" />
                                @error('cuit') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-3">
                                <label class="small mb-1" for="m_edad_desde">Edad desde</label>
                                <input wire:model="edad_desde" class="form-control form-control-sm" id="m_edad_desde"
                                    type="text" placeholder="edad_desde" />
                                @error('edad_desde') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1" for="m_edad_hasta">Edad hasta</label>
                                <input wire:model="edad_hasta" class="form-control form-control-sm" id="m_edad_hasta"
                                    type="text" placeholder="edad_hasta" />
                                @error('edad_hasta') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="m_porcentaje_adic">% adicional</label>
                                <input wire:model="porcentaje_adic" class="form-control form-control-sm"
                                    id="m_porcentaje_adic" type="text" placeholder="porcentaje_adic" />
                                @error('porcentaje_adic') <span class="text-danger small">{{ $message }}</span> @enderror
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
