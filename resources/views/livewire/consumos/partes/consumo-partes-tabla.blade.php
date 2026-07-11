<div>
    <div class="table-responsive">
        <table class="table table-striped table-hover" id="table_data">
            <thead class="thead">
                <tr>
                    <th>Nro</th>
                    <th>Centro</th>
                    <th>Profesional</th>
                    <th>Paciente</th>
                    <th>Fecha qx</th>
                    <th>Cobertura</th>
                    <th>Estado</th>
                    <th>Docs</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($partes as $item)
                    <tr wire:key="parte-{{ $item->id }}">
                        <td>
                            <span x-data x-init="new bootstrap.Tooltip($el)" data-bs-placement="top"
                                @if (!empty($item->name)) data-bs-title="usuario: {{ $item->name }} - cargado: {{ $item->created_at }}" @endif
                                class="badge bg-primary">{{ $item->id }}
                            </span>
                        </td>
                        <td>{{ $item->centro }}</td>
                        <td>{{ $item->profesional }}</td>
                        <td>{{ $item->paciente }}</td>
                        <td>{{ $item->fec_prestacion }}</td>
                        <td>{{ $item->sigla }}</td>
                        <td>
                            <span x-data x-init="new bootstrap.Tooltip($el)" data-bs-placement="top"
                                @if (!empty($item->observacion)) data-bs-title="{{ $item->observacion }}" @endif
                                class="badge bg-{{ $item->est_id == 1
                                    ? 'primary'
                                    : ($item->est_id == 2
                                        ? 'danger'
                                        : ($item->est_id == 3
                                            ? 'warning'
                                            : ($item->est_id == 4
                                                ? 'info'
                                                : ($item->est_id == 5
                                                    ? 'secondary'
                                                    : 'success')))) }}">{{ $item->est_descripcion }}
                                @if (!empty($item->observacion))
                                    <span class="badge text-bg-dark"> </span>
                                @endif
                            </span>
                        </td>
                        <td>{{ $item->cantidad }}</td>
                        <td class="td-actions">
                            <a class="btn btn-sm btn-success" href="{{ route('consumos.cargar', $item->id) }}"
                                x-data x-init="new bootstrap.Tooltip($el)" data-bs-placement="top"
                                data-bs-title="Consultar documentos cargados e ingresar consumo a facturar">
                                <i class="fa fa-fw fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                @if ($partes->isEmpty())
                    <tr>
                        <td colspan="9" class="text-center">No hay cargados consumos hasta el momento.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    @if ($partes->hasPages())
        {{ $partes->links() }}
    @endif
</div>
