@extends('layouts.main')

@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Gerenciadora cobertura padre') }}
                            </span>
                            <div class="float-right">
                                <a href="{{ route('gerenciadora_cobertura_padre.create') }}" 
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
                                    <tr><th>Id</th>
                                        <th>Gerenciadora</th>
										<th>Cobertura</th>
										<th>Padre</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($gerenciadora_cobertura_padre as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->gerenciadora->nombre }}</td>
											<td>{{ optional($item->cobertura)->sigla ?? '—' }}</td>
                                            <td>{{ $item->nomPadre->nombre }}</td>
                                            <td>
                                                @if($item->deleted_at)
                                                    <form id="restore_form_{{$item->id}}" action="{{ route('gerenciadora_cobertura_padre.restore', $item->id) }}" method="POST">
                                                        @csrf
                                                        <button type="button" class="btn btn-warning btn-sm " 
                                                            title="Volver a poner activa práctica o estudio que esta borrada." data-bs-toggle="tooltip"
                                                            onclick="confirmJob('¿Desea restaurar este registro?', 'restore_form_{{$item->id}}')">
                                                            <i class="fas fa-undo-alt"></i></button>
                                                    </form>
                                                @else
                                                    <form id="delete-form-{{ $item->id }}" action="{{ route('gerenciadora_cobertura_padre.destroy',$item->id) }}" method="POST">
                                                        <a class="btn btn-sm btn-success" href="{{ route('gerenciadora_cobertura_padre.edit', $item->id) }}"><i class="fa fa-fw fa-edit"></i></a>
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            title="Borrar combinación gerenciadora, cobertura, padre"
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
                @if(!empty($gerenciadora_cobertura_padre))
                    {!! $gerenciadora_cobertura_padre->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif
            </div>
        </div>
    </div>

@endsection
