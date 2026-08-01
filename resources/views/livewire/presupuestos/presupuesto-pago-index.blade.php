<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">

                        <span id="card_title">
                            {{ "Pagos del presupuesto - {$presupuestosCab->id} - {$presupuestosCab->fecha} - {$presupuestosCab->nombre} -  " . number_format($pagos->sum('valor'), 2, ',', '.') }}
                        </span>
                        <div class="float-right">
                            <a href="{{ route('presupuestos.cab.edit', $presupuestosCab->id) }}" title="Volver">
                                <button class="btn btn-warning btn-sm float-right">
                                    <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('utiles.alerts')
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th class="text-end">Valor</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pagos as $item)
                                    <tr wire:key="pago-{{ $item->id }}">
                                        <td>{{ $item->fecha }}</td>
                                        <td>{{ $item->user->name }}</td>
                                        <td class="text-end">{{ number_format($item->valor, 2, ',', '.') }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm"
                                                title="Borrar pago"
                                                onclick="confirmDelete({{ $item->id }}, 'Esta acción es irreversible.', () => @this.destroy({{ $item->id }}))">
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

            <div class="card mt-3 p-3 border">
                <div class="card-body">
                    <form wire:submit="guardar" novalidate>
                        <input type="hidden" wire:model="presupuestoCabId">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-3">
                                <label class="small mb-1">Usuario</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->name }}" disabled>
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1" for="fecha">Fecha</label>
                                <input wire:model="fecha" type="date" class="form-control" id="fecha" required />
                                @error('fecha') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1" for="valor">Valor ($)</label>
                                <input wire:model="valor" type="text" class="form-control" id="valor" required />
                                @error('valor') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-12">
                                <label class="small mb-1" for="observaciones">Observaciones</label>
                                <textarea wire:model="observaciones" class="form-control" id="observaciones" rows="3"
                                    placeholder=""></textarea>
                            </div>
                        </div>
                        <div class="box-footer mt20">
                            <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
