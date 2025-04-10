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
                            'S' => ['texto' => 'Saldado', 'clase' => 'bg-primary'],
                        ];
                    @endphp
                    <div class="card-body">
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
                                        <th>Estado</th>
                                        <th style="width: 15%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($presupuestosCab as $item)
                                        <tr>
                                            @php
                                                $total = 0;
                                                foreach ($item->presupuestosDet as $det) {
                                                    $total += $det->importe;
                                                }
                                            @endphp
                                            @if ($item->estado == 'P')
                                                @php
                                                    $total = $item->pagos->sum('importe');
                                                @endphp
                                            @endif
                                    
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->centro->nombre }}</td>
                                            <td>{{ $item->fecha }}</td>
                                            <td>{{ $item->paciente }}</td>
                                            <td>{{ $item->profesional?->nombre }}</td>
                                            <td>{{ $item->user->name }}</td>
                                            <td>{{ $item->id }}</td>
                                            <td>
                                                <span class="badge rounded-pill {{ $estados[$item->estado]['clase'] }}">
                                                    {{ $estados[$item->estado]['texto'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <form id="delete-form-{{ $item->id }}"
                                                    action="{{ route('presupuestos.cab.destroy', $item->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " 
                                                        href="{{ route('presupuestos.cab.payments',$item->id) }}">
                                                        <i class="fa fa-dollar-sign"></i>
                                                    </a>
                                                    <a class="btn btn-sm btn-success"
                                                        href="{{ route('presupuestos.cab.edit', $item->id) }}"><i
                                                            class="fa fa-fw fa-edit"></i>
                                                    </a>
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Delete Usuario"
                                                        onclick="confirmDelete({{ $item->id }})"><i
                                                            class="far fa-trash-alt text-white"></i>
                                                    </button>
                                                </form>
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
