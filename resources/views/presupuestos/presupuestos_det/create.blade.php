@extends('layouts.main')

@section('contenido')
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
                                class="btn btn-primary btn-sm shadow-sm" 
                                title="Crear parte">
                                    <i class="fa fa-plus-circle me-2" aria-hidden="true"></i>   Crear parte
                                </a>

                                <a href="{{ route('presupuestos.cab.edit', $presupuestosCab->id) }}" 
                                class="btn btn-warning btn-sm shadow-sm" 
                                title="Volver">
                                    <i class="fa fa-arrow-left me-2" aria-hidden="true"></i> Volver
                                </a>

                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>Cobertura</th>
                                        <th>Prestación</th>
                                        <th>%</th>
                                        <th>SubTotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($presupuestosDet as $item)
                                        <tr>
                                            <td>{{ $item->cobertura->nombre }}</td>
                                            <td>{{ $item->descripcion }}</td>
                                            <td>{{ $item->porcentaje }}</td>
                                            <td>{{ number_format((float) $item->valor, 2, ',', '.') }}</td>
                                            <td>
                                                <form id="delete-form-{{ $item->id }}"
                                                    action="{{ route('presupuestos.det.destroy', [$item->presupuesto_cab_id, $item->id]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Borrar práctica o estudio"
                                                        onclick="confirmDelete({{ $item->id }})"><i
                                                            class="far fa-trash-alt text-white"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {{-- @if (!empty($presupuestosDet))
                    {!! $presupuestosDet->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif --}}
                @include('presupuestos.presupuestos_det.form')
            </div>
        </div>
    </div>
@endsection
