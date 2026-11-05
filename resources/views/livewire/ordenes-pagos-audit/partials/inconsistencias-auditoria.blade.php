@php
    $items = $items ?? [];
    $titulo = $titulo ?? 'Auditoría cancelada: estos datos del archivo no tienen código para relacionar.';
    $nota = $nota ?? null;
    $clase = $clase ?? '';
@endphp

@if ($items !== [])
    <div class="alert alert-danger py-2 small {{ $clase }}">
        <div class="fw-semibold mb-1">
            <i class="fa-solid fa-ban me-1"></i>{{ $titulo }}
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered bg-white mb-2 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Atención</th>
                        <th>Obra social</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>%</th>
                        <th>Motivo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        @php($tipoInconsistencia = $item['tipo'] ?? '')
                        <tr>
                            <td>{{ $item['atencion'] ?? '—' }}</td>
                            <td>{{ $item['obra_social'] ?? '—' }}</td>
                            <td>{{ $item['codigo'] ?? '—' }}</td>
                            <td>{{ $item['descripcion'] ?? '—' }}</td>
                            <td>
                                @if (($item['porcentaje'] ?? null) !== null)
                                    {{ rtrim(rtrim(number_format((float) $item['porcentaje'], 2, ',', '.'), '0'), ',') }} %
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $item['motivo'] ?? '' }}</td>
                            <td class="text-nowrap">
                                @if ($tipoInconsistencia === 'ioma')
                                    @can('adm_consumos')
                                        <a class="btn btn-xs btn-outline-primary"
                                            href="{{ route('nomenclador.index', ['nom_padre' => 2, 'text' => $item['codigo']]) }}"
                                            target="_blank" rel="noopener">
                                            Codificar
                                        </a>
                                    @endcan
                                @elseif ($tipoInconsistencia === 'obra_social')
                                    @can('adm_entidades')
                                        <a class="btn btn-xs btn-outline-primary"
                                            href="{{ route('coberturas.index', ['text' => $item['codigo']]) }}"
                                            target="_blank" rel="noopener">
                                            Codificar
                                        </a>
                                    @endcan
                                @elseif ($tipoInconsistencia === 'atencion')
                                    @can('adm_partes')
                                        <a class="btn btn-xs btn-outline-primary"
                                            href="{{ route('partes_cab.filtrar', ['atencion' => $item['atencion']]) }}"
                                            target="_blank" rel="noopener">
                                            Ver parte
                                        </a>
                                    @endcan
                                @else
                                    @can('adm_consumos')
                                        <a class="btn btn-xs btn-outline-primary"
                                            href="{{ route('niveles.index', ['text' => $item['codigo'] ?: $item['descripcion']]) }}"
                                            target="_blank" rel="noopener">
                                            Codificar
                                        </a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($nota)
            <div class="mb-0">{{ $nota }}</div>
        @endif
        <div class="mt-2 text-end">
            <a href="{{ route('ordenes_pagos_audit.cab') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Volver a cabecera
            </a>
        </div>
    </div>
@endif
