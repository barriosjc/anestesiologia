<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Listas de precios') }}
                            </span>

                            <div class="float-right">
                                <button wire:click="abrirModal" class="btn btn-primary btn-sm float-right"
                                    data-placement="left">
                                    {{ __('Nuevo') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('utiles.alerts')

                        <div class="row align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="small mb-1" for="gerenciadora_id">Gerenciadoras</label>
                                <select wire:model="filtroGerenciadoraId" class="form-select form-select-sm" id="gerenciadora_id">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($gerenciadoras as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="cobertura_id">Coberturas</label>
                                <select wire:model="filtroCoberturaId" class="form-select form-select-sm" id="cobertura_id">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($coberturas as $item)
                                        <option value="{{ $item->id }}">{{ $item->sigla }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1" for="centro_id">Centros</label>
                                <select wire:model="filtroCentroId" class="form-select form-select-sm" id="centro_id">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($centros as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1" for="periodo">Periodo</label>
                                <select wire:model="filtroPeriodo" class="form-select form-select-sm" id="periodo">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($periodos as $item)
                                        <option value="{{ $item->nombre }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="small mb-1" for="grupo">Grupo</label>
                                <input type="text" wire:model="filtroGrupo" class="form-control form-control-sm"
                                    id="grupo">
                            </div>
                            <div class="col-md-1 d-flex gap-1">
                                <button wire:click="filtrar" class="btn btn-primary btn-sm">
                                    Buscar
                                </button>
                                <button wire:click="limpiar" class="btn btn-warning btn-sm">
                                    Limpiar
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>Gerenciadora</th>
                                        <th>Cobertura</th>
                                        <th>Centro</th>
                                        <th>Periodo</th>
                                        <th>Grupo</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($listas as $item)
                                        <tr wire:key="lista-{{ $item->id }}">
                                            <td>{{ $item->gerenciadora->nombre }}</td>
                                            <td>{{ $item->cobertura->sigla }}</td>
                                            <td>{{ $item->centro->nombre }}</td>
                                            <td>{{ $item->periodo }}</td>
                                            <td>{{ $item->grupo }}</td>
                                            <td class="text-center">
                                                <a class="btn btn-sm btn-primary"
                                                    href="{{ route('nomenclador.valores.filtrar', ['grupo' => $item->grupo]) }}"
                                                    title="Ver valores"><i class="fa fa-fw fa-eye"></i></a>
                                                <button type="button" class="btn btn-sm btn-success"
                                                    title="Modificar lista de precio"
                                                    wire:click="abrirModal({{ $item->id }})">
                                                    <i class="fa fa-fw fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    title="Borrar lista de precio"
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
                @if ($listas->hasPages())
                    {{ $listas->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- modal crear/editar lista --}}
    <div class="modal fade" id="listaModal" tabindex="-1" aria-labelledby="listaModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="listaModalLabel">
                        {{ $listaId ? 'Modificar' : 'Crear' }} Listas de precios
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardar" novalidate>
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="gerenciadora_id">Gerenciadoras</label>
                                <select wire:model="gerenciadora_id" class="form-select form-select-sm"
                                    id="m_gerenciadora_id">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($gerenciadoras as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('gerenciadora_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="cobertura_id">Coberturas</label>
                                <select wire:model="cobertura_id" class="form-select form-select-sm"
                                    id="m_cobertura_id">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($coberturas as $item)
                                        <option value="{{ $item->id }}">{{ $item->sigla }}</option>
                                    @endforeach
                                </select>
                                @error('cobertura_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="centro_id">Centros</label>
                                <select wire:model="centro_id" class="form-select form-select-sm" id="m_centro_id">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($centros as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('centro_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="periodo">Periodo</label>
                                <select wire:model="periodo" class="form-select form-select-sm" id="m_periodo">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($periodos as $item)
                                        <option value="{{ $item->nombre }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('periodo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="grupo">Grupo</label>
                                <input type="text" wire:model="grupo" class="form-control form-control-sm"
                                    id="m_grupo">
                                @error('grupo') <span class="text-danger small">{{ $message }}</span> @enderror
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
