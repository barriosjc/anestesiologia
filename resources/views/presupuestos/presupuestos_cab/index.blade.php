@extends('layouts.main')

@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Presupuestos') }}
                            </span>

                            <div class="float-right">
                                <a href="{{ route('presupuestos.cab.create') }}" class="btn btn-primary btn-sm float-right"
                                    data-placement="left">
                                    {{ __('Nuevo') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @php
                        $estados = [
                            'I' => ['texto' => 'Ingresado', 'clase' => 'bg-secondary'],
                            'P' => ['texto' => 'Pagado', 'clase' => 'bg-success'],
                            'C' => ['texto' => 'Cancelado', 'clase' => 'bg-danger'],
                            'O' => ['texto' => 'Cobrado', 'clase' => 'bg-info'],
                            'F' => ['texto' => 'Facturado', 'clase' => 'bg-primary'],
                        ];
                    @endphp
                    <div class="card-body">
                        @include('presupuestos.presupuestos_cab.filtros')
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="table_data">
                                <thead class="thead">
                                    <tr>
                                        <th>Nro</th>
                                        <th>Centro</th>
                                        <th>Fecha</th>
                                        <th>Paciente</th>
                                        <th>Profesional</th>
                                        <th>Usuario</th>
                                        <th>Total</th>
                                        <th>Pagado</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($presupuestosCab as $item)
                                        <tr>
                                            {{-- @php
                                                $total = 0;
                                                foreach ($item->presupuestosDet as $det) {
                                                    $total += $det->importe;
                                                }
                                            @endphp
                                            @if ($item->estado == 'P')
                                                @php
                                                    $total = $item->pagos->sum('importe');
                                                @endphp
                                            @endif --}}

                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->centro }}</td>
                                            <td>{{ $item->fecha }}</td>
                                            <td>{{ $item->nombre }}</td>
                                            <td>{{ $item->profesional }}</td>
                                            <td>{{ $item->usuario }}</td>
                                            <td>{{ number_format($item->total_presupuesto ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ number_format($item->total_pagado ?? 0, 2, ',', '.') }}</td>
                                            <td>
                                                <span class="badge rounded-pill {{ $estados[$item->estado]['clase'] }}">
                                                    {{ $estados[$item->estado]['texto'] }}
                                                </span>
                                            </td>
                                            <td>
                                                {{-- <form id="delete-form-{{ $item->id }}"
                                                    action="{{ route('presupuestos.cab.destroy', $item->id) }}"
                                                    method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE') --}}

                                                <!-- Dropdown con tres puntos -->
                                                <div class="dropdown d-inline">
                                                    <button class="btn btn-sm btn-gray" type="button"
                                                        id="dropdownMenuButton{{ $item->id }}"
                                                        data-bs-toggle="dropdown" aria-expanded="false"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-title="Acciones">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>

                                                    <ul class="dropdown-menu"
                                                        aria-labelledby="dropdownMenuButton{{ $item->id }}">
                                                        <li>
                                                            {{-- <a class="dropdown-item"
                                                                href="{{ route('presupuestos.pagos.create', $item->id) }}">
                                                                <i class="fa fa-dollar-sign me-2"></i> Cargar cobros
                                                            </a> --}}
                                                            <a class="dropdown-item" 
                                                                    href="{{ route('presupuestos.pagos.create', $item->id) }}" 
                                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Cargar los pagos que hacen los pacientes">
                                                                <i class="fa fa-dollar-sign me-2"></i> Cargar cobros
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('presupuestos.cab.edit', $item->id) }}"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Editar presupuesto">
                                                                <i class="fa fa-fw fa-edit me-2"></i> Editar
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('presupuestos.cab.print', $item->id) }}"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Imprimir presupuesto en formato pdf"
                                                                target="_blank">
                                                                <i class="fa fa-fw fa-print me-2"></i> Imprimir PDF
                                                            </a>
                                                        </li>
                                                        <form id="pago_form_{{ $item->id }}"
                                                            action="{{ route('presupuestos.cab.pagado', $item->id) }}"
                                                            method="GET" style="display: inline;">
                                                            <li>
                                                                <button type="button" class="dropdown-item"
                                                                    onclick="confirmJob('¿Desea marcar este presupuesto como pagado al anestesiólogo?', 'pago_form_{{ $item->id }}')"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Marcar como pagado al anestesiólogo">
                                                                    <i class="fas fa-hand-holding-usd me-2"></i> Marcar
                                                                    como pagado
                                                                </button>
                                                            </li>
                                                        </form>

                                                        <li>
                                                            <a class="dropdown-item"
                                                                    href="{{ route('presupuestos.cab.partes', $item->id) }}"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Genera el parte para facturar a la institución">
                                                                <i class="fas fa-coins me-2"></i> Generar parte
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <form id="delete-form-{{ $item->id }}"
                                                            action="{{ route('presupuestos.cab.destroy', $item->id) }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <li>
                                                                <button type="button" class="dropdown-item text-danger"
                                                                    onclick="confirmDelete({{ $item->id }})">
                                                                    <i class="far fa-trash-alt me-2"></i> Eliminar
                                                                </button>
                                                            </li>
                                                        </form>

                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if (!empty($presupuestosCab))
                    {!! $presupuestosCab->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif
            </div>
        </div>
    </div>
@endsection
