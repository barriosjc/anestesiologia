<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header py-2">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Valorización') }}
                            </span>
                            <div class="form-group float-right">
                                <a href="{{ request('back', url()->previous()) }}" title="Volver">
                                    <button class="btn btn-warning btn-sm float-right">
                                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                                    </button>
                                </a>
                                <div class="btn btn-sm btn-success float-right" data-bs-toggle="tooltip"
                                    title="Copiar de una lista de precios existente y crea una nueva Lista de precios con el grupo ingresado, este nuevo no debe existir."
                                    wire:click="abrirModalNuevo">
                                    <span>{{ __('Nuevo') }}</span>
                                </div>
                                <div class="btn btn-sm btn-primary float-right" data-bs-toggle="tooltip"
                                    title="Copiar una lista de precios de un grupo a otro grupo."
                                    wire:click="$dispatch('open-modal', { modal: 'copiarModal' })">
                                    <span>{{ __('Copiar') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('utiles.alerts')

                        <div class="row align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="form-label small" for="grupo">Grupo</label>
                                <input wire:model="filtroGrupo" class="form-control" id="grupo" type="text"
                                    placeholder="grupo" />
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small" for="nivel">Nivel</label>
                                <select wire:model="filtroNivel" class="form-select" id="nivel">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($niveles as $item)
                                        <option value="{{ $item->nivel }}">{{ $item->nivel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 d-flex gap-2">
                                <button wire:click="filtrar" class="btn btn-primary">
                                    Filtrar Listas
                                </button>
                                <button wire:click="limpiar" class="btn btn-warning">
                                    Limpiar
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="table_data">
                                <thead class="thead">
                                    <tr>
                                        <th>Nro</th>
                                        <th>Grupo</th>
                                        <th>Nivel</th>
                                        <th>Tipo</th>
                                        <th class="text-end">Valor</th>
                                        <th>Moneda</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($valores as $item)
                                        <tr wire:key="valor-{{ $item->id }}">
                                            <td>{{ $valores->firstItem() + $loop->index }}</td>
                                            <td>{{ $item->grupo }}</td>
                                            <td>{{ $item->nivel }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $item->tipo == 1 ? 'primary' : 'success' }}">{{ $item->tipo == 1 ? 'Manual' : 'Factor' }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn btn-warning btn-small" data-bs-toggle="tooltip"
                                                    title="Modificar valor"
                                                    wire:click="abrirModalValor({{ $item->id }})">
                                                    <i class="fa-regular fa-pen-to-square icon-small"></i>
                                                </div>
                                                <span> $ {{ number_format((float) $item->valor, 2, ',', '.') }}</span>
                                            </td>
                                            <td>{{ $item->moneda }}</td>
                                            <td class="td-actions text-center">
                                                @if (empty($item->deleted_at))
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Enviar a papelera"
                                                        onclick="confirmDelete({{ $item->id }}, 'Esta acción es irreversible.', () => @this.borrar({{ $item->id }}))">
                                                        <i class="far fa-trash-alt text-white"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-success btn-sm"
                                                        title="Restaurar registro"
                                                        onclick="confirmJob('¿Desea restaurar este registro?', null, false, '', () => @this.borrar({{ $item->id }}))">
                                                        <i class="fa-solid fa-rotate-left"></i>
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
                @if ($valores->hasPages())
                    {{ $valores->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- modal editar valor --}}
    <div class="modal fade" id="valorModal" tabindex="-1" aria-labelledby="valorModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="valorModalLabel">$ o unidades, formato $: 1.200,50</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardarValor" novalidate>
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">$</span>
                            <input type="text" wire:model="modalValor" class="form-control" placeholder="Valor"
                                aria-label="Valor" aria-describedby="basic-addon1">
                        </div>
                        @error('modalValor') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- modal copiar grupo --}}
    <div class="modal fade" id="copiarModal" tabindex="-1" aria-labelledby="copiarModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Lista</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="copiarGrupo" novalidate>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="small mb-1" for="grupo_c">Grupo a copiar</label>
                                <input wire:model="grupoC" class="form-control" id="grupo_c" type="text"
                                    placeholder="grupo_c" />
                                @error('grupoC') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1" for="grupo_n">Nuevo Grupo</label>
                                <input wire:model="grupoN" class="form-control" id="grupo_n" type="text"
                                    placeholder="grupo_n" />
                                @error('grupoN') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1" for="porcentaje">% incremento</label>
                                <input wire:model="porcentaje" class="form-control" id="porcentaje" type="text"
                                    placeholder="porcentaje" title="Valor % permitido de -99 a 100"
                                    data-bs-toggle="tooltip" />
                                @error('porcentaje') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-sm btn-primary">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- modal nuevo valor --}}
    <div class="modal fade" id="nuevoValorModal" tabindex="-1" aria-labelledby="nuevoValorModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo valor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardarNuevo" novalidate>
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="nuevo_grupo">Grupo</label>
                                <input wire:model="nuevoGrupo" class="form-control" id="nuevo_grupo" type="text"
                                    placeholder="grupo" />
                                @error('nuevoGrupo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="nuevo_nivel">Nivel</label>
                                <input wire:model="nuevoNivel" class="form-control" id="nuevo_nivel" type="text"
                                    placeholder="Ingrese nivel" />
                                @error('nuevoNivel') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-4">
                                <label class="small mb-1" for="nuevo_tipo">Tipo (1:$ / 2:un)</label>
                                <input wire:model="nuevoTipo" class="form-control" id="nuevo_tipo" type="text"
                                    placeholder="Ingrese tipo de práctica" />
                                @error('nuevoTipo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1" for="nuevo_valor">Valor</label>
                                <input wire:model="nuevoValor" class="form-control" id="nuevo_valor" type="text"
                                    placeholder="Ingrese valor" />
                                @error('nuevoValor') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1" for="nuevo_moneda">Moneda</label>
                                <select wire:model="nuevoMoneda" class="form-select" id="nuevo_moneda">
                                    <option value="ARS">ARS</option>
                                    <option value="USD">USD</option>
                                </select>
                                @error('nuevoMoneda') <span class="text-danger small">{{ $message }}</span> @enderror
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
