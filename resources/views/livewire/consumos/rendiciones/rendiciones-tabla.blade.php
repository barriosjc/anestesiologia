<div>
    @if ($mensaje)
        <div class="alert alert-{{ $mensajeTipo }} py-2">{{ $mensaje }}</div>
    @endif
    @error('selected') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror

    <div id="contenedor-grilla" class="table-responsive">
        <table class="table table-striped table-hover" id="table_data">
            <thead class="thead">
                <tr>
                    <th>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:click="toggleAll($event.target.checked)">
                        </div>
                    </th>
                    <th>Nro</th>
                    <th>F.proc</th>
                    <th>Paciente</th>
                    <th class="columna-extra">Periodo</th>
                    <th>Práctica</th>
                    <th>%</th>
                    <th>Valor($)</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($partes as $item)
                    <tr wire:key="rendicion-{{ $item->consumos_det_id }}">
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="selected"
                                    value="{{ $item->consumos_det_id }}">
                            </div>
                        </td>
                        <td>
                            <span x-data x-init="new bootstrap.Tooltip($el)" data-bs-placement="top"
                                data-bs-title="Prof: {{ $item->prof_nombre }} - Cobertura: {{ $item->cob_sigla }} - Centro: {{ $item->cen_nombre }}"
                                class="badge bg-primary">{{ $item->parte_cab_id }}
                            </span>
                        </td>
                        <td>{{ $item->fec_prestacion }}</td>
                        <td>{{ $item->pac_nombre }}</td>
                        <td class="columna-extra">{{ $item->periodo }}</td>
                        <td>{{ $item->nivel . '/' . $item->codigo . '/' . $item->nom_descripcion }}</td>
                        <td>{{ $item->porcentaje }}</td>
                        <td class="text-end">{{ number_format((float) $item->valor, 2, ',', '.') }}</td>
                        <td>
                            <span x-data x-init="new bootstrap.Tooltip($el)" data-bs-placement="top"
                                @if (!empty($item->obs_refac)) data-bs-title="{{ $item->periodo . ' / ' . $item->obs_refac }}" @endif
                                class="badge bg-{{ $item->estado_id == 4
                                    ? 'primary'
                                    : ($item->estado_id == 5
                                        ? 'success'
                                        : ($item->estado_id == 6
                                            ? 'warning'
                                            : ($item->estado_id == 7
                                                ? 'info'
                                                : ($item->estado_id == 8
                                                    ? 'secondary'
                                                    : 'danger')))) }}">{{ $item->est_descripcion }}
                                @if (!empty($item->obs_refac))
                                    <span class="badge text-bg-dark"> </span>
                                @endif
                            </span>
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

    <hr>
    <div class="row align-items-end">
        <div class="form-group col-md-2">
            <label class="small mb-1" for="periodo">Periodo de rendición</label>
            <select wire:model="periodo" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
                @foreach ($periodos as $item)
                    <option value="{{ $item->nombre }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" wire:click="generarRendicion" class="btn btn-primary btn-sm">
                {{ __('Generar rendición') }}
            </button>
        </div>
    </div>

    <hr>
    <div x-data="{ estadoCambio: $wire.entangle('estadoCambio') }" class="row align-items-end pt-2">
        <div class="form-group col-md-2">
            <label class="small mb-1" for="estadoCambio">Cambio de estado</label>
            <select x-model="estadoCambio" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
                @foreach ($estados as $item)
                    <option value="{{ $item->id }}">{{ $item->descripcion }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2" x-show="estadoCambio == 7" style="display: none">
            <label class="small mb-1" for="periodoRefac">Periodo a refacturar</label>
            <select wire:model="periodoRefac" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
                @foreach ($periodos as $item)
                    <option value="{{ $item->nombre }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-3" x-show="estadoCambio == 7" style="display: none">
            <label class="small mb-1" for="obsRefac">Observaciones</label>
            <textarea class="form-control form-control-sm" wire:model="obsRefac" rows="3"
                placeholder="Por que pasa a refacturar? "></textarea>
        </div>
        <div class="col-md-2">
            <button type="button" wire:click="cambiarEstados" class="btn btn-info btn-sm">
                {{ __('Cambiar estados') }}
            </button>
        </div>
    </div>

    <hr>
    <div class="row align-items-end pt-2">
        <div class="form-group col-md-2">
            <label class="small mb-1" for="periodoRevalorizar">Periodo a revalorizar</label>
            <select wire:model="periodoRevalorizar" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
                @foreach ($periodos as $item)
                    <option value="{{ $item->nombre }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" wire:click="revalorizar" class="btn btn-warning btn-sm">
                {{ __('Revalorizar partes') }}
            </button>
        </div>
    </div>

    <hr>
    <div class="row align-items-end pt-2">
        <div class="form-group col-md-2">
            <label class="small mb-1" for="estadoAgregar">Agregar nuevo consumo</label>
            <select wire:model="estadoAgregar" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
                @foreach ($estados as $item)
                    <option value="{{ $item->id }}">{{ $item->descripcion }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2">
            <label class="small mb-1" for="periodoAgregar">Periodo</label>
            <select wire:model="periodoAgregar" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
                @foreach ($periodos as $item)
                    <option value="{{ $item->nombre }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-5">
            <label class="small mb-1" for="obsAgregar">Observaciones</label>
            <textarea class="form-control form-control-sm" wire:model="obsAgregar" rows="1"
                placeholder="Por que agrega nuevo consumo? "></textarea>
        </div>
        <div class="form-group col-md-2">
            <label class="small mb-1" for="valorAgregar">Valor ($)</label>
            <input type="text" class="form-control form-control-sm" wire:model="valorAgregar">
        </div>
        <div class="col-md-1">
            <button type="button" wire:click="agregarConsumo" class="btn btn-info btn-sm">
                {{ __('Guardar') }}
            </button>
        </div>
    </div>

    <hr>
    <div class="row align-items-end pt-2">
        <div class="form-group col-md-2">
            <label class="small mb-1" for="estadoAgregarYDiff">Nuevo consumo y diferencia</label>
            <select wire:model="estadoAgregarYDiff" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
                @foreach ($estados as $item)
                    <option value="{{ $item->id }}">{{ $item->descripcion }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2">
            <label class="small mb-1" for="periodoAgregarYDiff">Periodo</label>
            <select wire:model="periodoAgregarYDiff" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
                @foreach ($periodos as $item)
                    <option value="{{ $item->nombre }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-4">
            <label class="small mb-1" for="obsAgregarYDiff">Observaciones</label>
            <textarea class="form-control form-control-sm" wire:model="obsAgregarYDiff" rows="1"
                placeholder="Por que agrega nuevo consumo? "></textarea>
        </div>
        <div class="form-group col-md-1">
            <div>
                <input type="radio" id="refacturarydiff" wire:model="refacturarYDiff" value="refacturar" checked>
                <label class="small mb-1" for="refacturarydiff">Refacturar</label>
            </div>
            <div>
                <input type="radio" id="auditoriaydiff" wire:model="refacturarYDiff" value="auditoria">
                <label class="small mb-1" for="auditoriaydiff">Auditoría</label>
            </div>
        </div>
        <div class="form-group col-md-2">
            <label class="small mb-1" for="valorAgregarYDiff">Valor ($)</label>
            <input type="text" class="form-control form-control-sm" wire:model="valorAgregarYDiff">
        </div>
        <div class="col-md-1">
            <button type="button" wire:click="agregarConsumoYDiferencia" class="btn btn-info btn-sm">
                {{ __('Guardar') }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const contenedorGrilla = document.getElementById('contenedor-grilla');
            const columnasExtra = document.querySelectorAll('.columna-extra');

            const ajustarColumnas = () => {
                const anchoGrilla = contenedorGrilla.offsetWidth;
                if (anchoGrilla < 1250) {
                    columnasExtra.forEach(col => col.classList.add('d-none'));
                } else {
                    columnasExtra.forEach(col => col.classList.remove('d-none'));
                }
            };

            window.addEventListener('resize', ajustarColumnas);
            const observer = new ResizeObserver(ajustarColumnas);
            observer.observe(contenedorGrilla);
            ajustarColumnas();
        });
    </script>
@endpush
