@extends('layouts.main')

@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            @php($data = $nomenclador->first())
                            <span id="card_title">
                                Nomenclador de anestesiología ( {{ $data->nomPadre->nombre  }} )
                            </span>
                            <div class="float-right">
                                <a href="{{ route('nom_padres.index', $data->nom_padre_id) }}" title="Volver">
                                    <button class="btn btn-warning btn-sm float-right">
                                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                                    </button>
                                </a>
                                <a href="{{ route('nomenclador.create', $data->nom_padre_id) }}" 
                                    class="btn btn-primary btn-sm float-right" data-placement="left">
                                     {{ __('Nuevo') }}
                                 </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        {{-- <th>Id</th> --}}
                                        <th>Padre</th>
										<th>Código</th>
                                        <th>Nivel</th>
										<th>Nombre</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nomenclador as $item)
                                        <tr>
                                            {{-- <td>{{ $item->id }}</td> --}}
                                            <td>{{ $item->nom_padre_id }}</td>
											<td>{{ $item->codigo }}</td>
                                            <td>{{ $item->nivel }}</td>
                                            <td>{{ $item->descripcion }}</td>
                                            <td>
                                                @if($item->deleted_at)
                                                    <form id="restore_form_{{$item->id}}" action="{{ route('nomenclador.restore', $item->id) }}" method="POST">
                                                        @csrf
                                                        <button type="button" class="btn btn-warning btn-sm " 
                                                            title="Volver a poner activa la práctica  que esta borrada." data-bs-toggle="tooltip"
                                                            onclick="confirmJob('¿Desea restaurar este registro?', 'restore_form_{{$item->id}}')">
                                                            <i class="fas fa-undo-alt"></i></button>
                                                    </form>
                                                @else
                                                    <form id="delete-form-{{ $item->id }}" action="{{ route('nomenclador.destroy',$item->id) }}" method="POST">
                                                        <a class="btn btn-sm btn-primary " href="{{ route('nomenclador.valores.filtrar', ['nivel' => $item->codigo] ) }}"><i class="fa fa-dollar-sign"></i></a>
                                                        <a class="btn btn-sm btn-success" href="{{ route('nomenclador.edit', $item->id) }}"><i class="fa fa-fw fa-edit"></i></a>
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            title="Borrar práctica"
                                                            onclick="confirmDelete({{ $item->id }})"><i
                                                                class="far fa-trash-alt text-white"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if(!empty($nomenclador))
                    {!! $nomenclador->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif
            </div>
        </div>
    </div>

@endsection
