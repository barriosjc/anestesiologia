<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">

                        <span id="card_title">
                            {{ "Detalle del presupuesto - {$presupuestosCab->fecha} - {$presupuestosCab->nombre}" }}
                        </span>

                        <div class="d-flex align-items-center gap-2 ms-auto">

                            <a href="{{ route('presupuestos.cab.partes', $presupuestosCab->id) }}"
                                class="btn btn-primary btn-sm shadow-sm" title="Crear parte">
                                <i class="fa fa-plus-circle me-2" aria-hidden="true"></i> Crear parte
                            </a>

                            <a href="{{ route('presupuestos.cab.edit', $presupuestosCab->id) }}"
                                class="btn btn-warning btn-sm shadow-sm" title="Volver">
                                <i class="fa fa-arrow-left me-2" aria-hidden="true"></i> Volver
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
                                    <th>Cobertura</th>
                                    <th>Prestación</th>
                                    <th class="text-end">%</th>
                                    <th class="text-end">SubTotal</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($presupuestosDet as $item)
                                    <tr wire:key="presupuesto-det-{{ $item->id }}">
                                        <td>{{ $item->cobertura->nombre }}</td>
                                        <td>{{ $item->descripcion }}</td>
                                        <td class="text-end">{{ $item->porcentaje }}</td>
                                        <td class="text-end">{{ number_format((float) $item->valor, 2, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if ($item->observaciones)
                                                <span class="badge rounded-pill bg-warning text-dark" x-data
                                                    x-init="new bootstrap.Tooltip($el)" data-bs-placement="top"
                                                    data-bs-title="{{ $item->observaciones }}">Obs.</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                title="Borrar práctica o estudio"
                                                onclick="confirmDelete({{ $item->id }}, 'Esta acción es irreversible.', () => @this.destroy({{ $item->id }}))"><i
                                                    class="far fa-trash-alt text-white"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <livewire:presupuestos.presupuesto-det-form :presupuesto-cab-id="$presupuestosCab->id"
                :key="'presupuesto-det-form-' . $presupuestosCab->id" />
        </div>
    </div>
</div>
