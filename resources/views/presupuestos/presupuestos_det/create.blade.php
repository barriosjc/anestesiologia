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
                            <div class="float-right">
                                <a href="{{ route('presupuestos.cab.create', ['id' => $presupuestosCab->id]) }}" title="Volver">
                                    <button class="btn btn-warning btn-sm float-right">
                                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr><th>Cobertura</th>
                                        <th>Prestación</th>
										<th>%</th>
										<th>SubTotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- @dd($presupuestosDet) --}}
                                    @foreach ($presupuestosDet as $item)
                                        <tr>
                                            <td>{{ $item->cobertura->nombre }}</td>
                                            <td>{{ $item->descripcion }}</td>
											<td>{{ $item->porcentaje }}</td>
                                            <td>{{ $item->valor }}</td>
                                            <td>
                                                {{-- @if($item->deleted_at)
                                                    <form id="restore_form_{{$item->id}}" action="{{ route('nom_practicas_estudios.restore', $item->id) }}" method="POST">
                                                        @csrf
                                                        <button type="button" class="btn btn-warning btn-sm " 
                                                            title="Volver a poner activa práctica o estudio que esta borrada." data-bs-toggle="tooltip"
                                                            onclick="confirmJob('¿Desea restaurar este registro?', 'restore_form_{{$item->id}}')">
                                                            <i class="fas fa-undo-alt"></i></button>
                                                    </form>
                                                @else --}}
                                                    <form id="delete-form-{{ $item->id }}" action="{{ route('presupuestos.det.destroy',[$item->presupuesto_cab_id,$item->id]) }}" method="POST">
                                                        {{-- <a class="btn btn-sm btn-primary " href="{{ route('nomenclador.valores.filtrar', ['nivel' => $item->codigo] ) }}"><i class="fa fa-dollar-sign"></i></a> --}}
                                                        {{-- <a class="btn btn-sm btn-success" href="{{ route('presupuestos.det.edit', [$item->presupuesto_cab_id,$item->id]) }}"><i class="fa fa-fw fa-edit"></i></a> --}}
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            title="Borrar práctica o estudio"
                                                            onclick="confirmDelete({{ $item->id }})"><i
                                                                class="far fa-trash-alt text-white"></i></button>
                                                    </form>
                                                {{-- @endif --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {{-- @if(!empty($presupuestosDet))
                    {!! $presupuestosDet->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif --}}
                @include('presupuestos.presupuestos_det.form')
            </div>
        </div>
    </div>

@endsection
