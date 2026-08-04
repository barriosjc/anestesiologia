<section class="content container-fluid">
    <div class="card card-default">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="card-title">Detalle del parte nro: {{ $parte_cab_id }}</span>
            <div>
                <div class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#valorModal">
                    Cambiar estado
                </div>
                <a href="{{ route('consumos.partes.filtrar') }}" class="btn btn-info btn-sm" data-placement="left">
                    Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif
            @if ($soloConsulta)
                <div class="alert alert-danger" role="alert">
                    Parte en modo consulta, solo se puede editar partes en estados: A liquidar o En facturación.
                </div>
            @endif
            <div class="alert alert-info" role="alert">
                {{ "{$data->sigla} / {$data->centro} / {$data->profesional} / {$data->paciente} ({$data->edad}) / {$data->fec_prestacion} / Obs: {$observaciones}" }}
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tabla_data">
                    <thead class="thead">
                        <tr>
                            <th>Nro hoja</th>
                            <th>Documento</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($partes_det as $item)
                            <tr style="height: 30px;">
                                <td style="padding: 5px;">{{ $item->nro_hoja }}</td>
                                <td style="padding: 5px;">{{ $item->documento->nombre }}</td>
                                <td class="text-end" style="padding: 5px;">
                                    <a class="btn btn-sm btn-warning" target="_blank"
                                        href="{{ route('partes_det.download', $item->id) }}">
                                        <i class="fa fa-fw fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        @if ($partes_det->isEmpty())
                            <tr style="height: 30px;">
                                <td colspan="3" class="text-center" style="padding: 5px;">No hay documentación cargada hasta el momento.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <hr>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tabla_consumo">
                    <thead class="thead">
                        <tr>
                            <th>Practica</th>
                            <th>%</th>
                            <th>Valor ($)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($consumos as $item)
                            <tr wire:key="consumo-{{ $item->id }}">
                                <td>{{ $item->nivel . ' / ' . $item->codigo . ' / ' . $item->nom_descripcion }}</td>
                                <td>{{ $item->porcentaje }}</td>
                                <td>{{ number_format((float) $item->valor, 2, ',', '.') }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-danger"
                                        onclick="confirmDelete({{ $item->id }}, 'Esta acción es irreversible.', () => @this.destroy({{ $item->id }}))">
                                        <i class="fa fa-fw fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        @if ($consumos->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center">No hay cargados consumos hasta el momento.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <hr>

            <livewire:consumos.cargar.consumo-cargar-form
                :parte-cab-id="$parte_cab_id"
                :gerenciadora-id="$data->gerenciadora_id"
                :cobertura-id="$data->cobertura_id"
                :nom-padre-json="$nom_padre_json"
                :solo-consulta="$soloConsulta"
                :key="'consumo-form-'.$parte_cab_id" />
        </div>
    </div>

    <div class="modal fade" id="valorModal" tabindex="-1" aria-labelledby="valorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="valorModalLabel">Pasar el parte a observado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit="observar">
                    <div class="modal-body">
                        <label class="label-control" for="estado_cambio">Estados</label>
                        <select class="form-select form-select-sm @error('estado_cambio') is-invalid @enderror"
                            id="estado_cambio" wire:model="estado_cambio">
                            <option value="">-- Seleccione --</option>
                            @foreach ($estados as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->descripcion }}
                                </option>
                            @endforeach
                        </select>
                        @error('estado_cambio')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <label class="label-control">Observaciones</label>
                        <textarea rows="4" wire:model="observaciones"
                            class="form-control @error('observaciones') is-invalid @enderror"></textarea>
                        @error('observaciones')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
