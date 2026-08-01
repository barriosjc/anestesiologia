<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                Nomencladores
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
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nomencladores as $item)
                                        <tr wire:key="nom-padre-{{ $item->id }}">
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->nombre }}</td>
                                            <td>
                                                @if ($item->tipo == 'a')
                                                    <a class="btn btn-sm btn-primary "
                                                        href="{{ route('nomenclador.index', $item->id) }}"><i
                                                            class="fa fa-fw fa-eye"></i></a>
                                                @else
                                                    <a class="btn btn-sm btn-primary "
                                                        href="{{ route('nom_practicas_estudios.index', $item->id) }}"><i
                                                            class="fa fa-fw fa-eye"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if ($nomencladores->hasPages())
                    {{ $nomencladores->links() }}
                @endif
            </div>
        </div>
    </div>

    {{-- modal crear nomenclador padre --}}
    <div class="modal fade" id="nomPadreModal" tabindex="-1" aria-labelledby="nomPadreModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear nomenclador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="guardar" novalidate>
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-4">
                                <label class="small mb-1" for="np_tipo">Tipo</label>
                                <input wire:model="tipo" class="form-control form-control-sm" id="np_tipo" type="text"
                                    placeholder="a / n" />
                                @error('tipo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="small mb-1" for="np_nombre">Nombre</label>
                                <input wire:model="nombre" class="form-control form-control-sm" id="np_nombre"
                                    type="text" placeholder="Ingrese descripción del nomenclador" />
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
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
