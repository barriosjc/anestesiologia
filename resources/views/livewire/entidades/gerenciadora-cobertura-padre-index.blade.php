<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Gerenciadora cobertura padre') }}
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
                                        <th>Id</th>
                                        <th>Gerenciadora</th>
                                        <th>Cobertura</th>
                                        <th>Padre</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($gerenciadora_cobertura_padre as $item)
                                        <tr wire:key="geren-{{ $item->id }}">
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->gerenciadora->nombre }}</td>
                                            <td>{{ optional($item->cobertura)->sigla ?? '—' }}</td>
                                            <td>{{ $item->nomPadre->nombre }}</td>
                                            <td class="td-actions text-center">
                                                @if (empty($item->deleted_at))
                                                    <button type="button" class="btn btn-sm btn-success"
                                                        data-bs-toggle="tooltip" data-bs-title="Editar relación"
                                                        wire:click="abrirModalEditar({{ $item->id }})">
                                                        <i class="fa fa-fw fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Borrar combinación gerenciadora, cobertura, padre"
                                                        onclick="confirmDelete({{ $item->id }}, 'Esta acción es irreversible.', () => @this.borrar({{ $item->id }}))">
                                                        <i class="far fa-trash-alt text-white"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-warning btn-sm"
                                                        title="Volver a poner activa la relación que esta borrada."
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
                @if ($gerenciadora_cobertura_padre->hasPages())
                    {{ $gerenciadora_cobertura_padre->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- modal crear/editar relación --}}
    <div class="modal fade" id="gerenciadoraCoberturaPadreModal" tabindex="-1"
        aria-labelledby="gerenciadoraCoberturaPadreModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalId ? 'Modificar' : 'Crear' }} relación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardar" novalidate>
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-4">
                                <label class="small mb-1" for="m_gerenciadora_id">Gerenciadora</label>
                                <select wire:model="gerenciadora_id" class="form-select form-select-sm"
                                    id="m_gerenciadora_id" required>
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($gerenciadoras as $data)
                                        <option value="{{ $data->id }}">{{ $data->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('gerenciadora_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1" for="m_cobertura_id">Coberturas</label>
                                <select wire:model="cobertura_id" class="form-select form-select-sm"
                                    id="m_cobertura_id">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($coberturas as $data)
                                        <option value="{{ $data->id }}">{{ $data->sigla }}</option>
                                    @endforeach
                                </select>
                                @error('cobertura_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1" for="m_nom_padre_id">Padre</label>
                                <select wire:model="nom_padre_id" class="form-select form-select-sm"
                                    id="m_nom_padre_id">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($nom_padres as $data)
                                        <option value="{{ $data->id }}">{{ $data->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('nom_padre_id') <span class="text-danger small">{{ $message }}</span> @enderror
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
