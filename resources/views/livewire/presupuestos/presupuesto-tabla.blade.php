<div>
    @include('utiles.alerts')

    <div class="table-responsive overflow-visible">
        <table class="table table-striped table-hover" id="table_data">
            <thead class="thead">
                <tr>
                    <th class="text-end">Nro</th>
                    <th>Centro</th>
                    <th>Fecha</th>
                    <th>Paciente</th>
                    <th>Profesional</th>
                    <th>Usuario</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Pagado</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($presupuestosCab as $item)
                    <tr wire:key="presupuesto-{{ $item->id }}">
                        <td class="text-end">{{ $item->id }}</td>
                        <td>{{ $item->centro }}</td>
                        <td>{{ $item->fecha }}</td>
                        <td>{{ $item->nombre }}</td>
                        <td>{{ $item->profesional }}</td>
                        <td>{{ $item->usuario }}</td>
                        <td class="text-end">{{ number_format($item->total_presupuesto ?? 0, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->total_pagado ?? 0, 2, ',', '.') }}</td>
                        <td>
                            <span class="badge rounded-pill {{ $estados[$item->estado]['clase'] }}"
                                @if ($item->estado == 'F') data-bs-toggle="tooltip" data-bs-placement="top" title="Parte Nro: {{ $item->parte_cab_id }}" @endif>
                                {{ $estados[$item->estado]['texto'] }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown d-inline">
                                <button class="btn btn-sm btn-gray" type="button"
                                    id="dropdownMenuButton{{ $item->id }}" data-bs-toggle="dropdown"
                                    aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-title="Acciones">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>

                                <ul class="dropdown-menu border border-2 border-secondary shadow"
                                    aria-labelledby="dropdownMenuButton{{ $item->id }}">
                                    @if ($item->estado != 'C')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('presupuestos.pagos.create', $item->id) }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Cargar los pagos que hacen los pacientes">
                                                <i class="fa fa-dollar-sign me-2"></i> Cargar cobros
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('presupuestos.cab.edit', $item->id) }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Editar presupuesto">
                                                <i class="fa fa-fw fa-edit me-2"></i> Editar
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('presupuestos.cab.print', $item->id) }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Imprimir presupuesto en formato pdf" target="_blank">
                                                <i class="fa fa-fw fa-print me-2"></i> Imprimir PDF
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item"
                                                onclick="confirmJob('¿Desea marcar este presupuesto como pagado al anestesiólogo?', null, false, '', () => @this.pagado({{ $item->id }}))"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Marcar como pagado al anestesiólogo">
                                                <i class="fas fa-hand-holding-usd me-2"></i> Marcar como pagado
                                            </button>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('presupuestos.cab.partes', $item->id) }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Genera el parte para facturar a la institución">
                                                <i class="fas fa-coins me-2"></i> Generar parte
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger"
                                                onclick="confirmDelete({{ $item->id }}, 'Esta acción es irreversible.', () => @this.destroy({{ $item->id }}))">
                                                <i class="far fa-trash-alt me-2"></i> Eliminar
                                            </button>
                                        </li>
                                    @else
                                        <li>
                                            <button type="button" class="dropdown-item text-danger"
                                                onclick="confirmJob('¿Desea restaurar este registro?', null, false, '', () => @this.restaurar({{ $item->id }}))">
                                                <i class="fas fa-history me-2"></i> Restaurar presupuesto
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($presupuestosCab->hasPages())
        {{ $presupuestosCab->links() }}
    @endif
</div>
