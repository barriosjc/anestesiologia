<div>
    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover" id="table_data">
            <thead class="thead">
                <tr>
                    <th style="width: 7%">Nro</th>
                    <th>Centro</th>
                    <th>Profesional</th>
                    <th>Paciente</th>
                    <th>Fecha qx</th>
                    <th>Cobertura</th>
                    <th>Estado</th>
                    <th style="width: 5%"><i class="fa-solid fa-paperclip"></i></th>
                    <th style="width: 16%">(Partes: {{ $partes->total() }})</th>
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
                            <a class="btn btn-sm btn-info"
                                wire:click="abrirEstadoModal({{ $item->id }}, @js($item->observacion))"
                                x-data x-init="new bootstrap.Tooltip($el)" data-bs-placement="top"
                                data-bs-title="Cargado todo el parte, ahora para pasar: A liquidar, click aquí.">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                            <a class="btn btn-sm btn-success" href="{{ route('partes_cab.edit', $item->id) }}">
                                <i class="fa fa-fw fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" title="Delete parte"
                                onclick="confirmDelete({{ $item->id }}, 'Esta acción es irreversible.', () => @this.destroy({{ $item->id }}))">
                                <i class="far fa-trash-alt text-white"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                @if ($partes->isEmpty())
                    <tr>
                        <td colspan="9" class="text-center">No hay cargados partes hasta el momento.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    @if ($partes->hasPages())
        {{ $partes->links() }}
    @endif

    <div>
        @include('cargas.cab.partials.cambio_estado')
    </div>
</div>

@push('scripts')
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
@endpush
