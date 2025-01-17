@extends('layouts.main')

@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Profesionales') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('profesionales.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        <th>Nro</th>
										<th>Nombre</th>
										<th>Emai</th>
										<th>DNI</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($profesionales as $item)
                                        <tr>
                                            <td>{{ ++$i }}</td>
											<td>{{ $item->nombre }}</td>
                                            <td>{{ $item->email }}</td>
											<td>{{ $item->dni }}</td>

                                            <td>
                                                <form id="delete-form-{{ $item->id }}" action="{{ route('profesionales.destroy', $item->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('profesional.cargar.documentacion', $item->id) }}"><i class="fa fa-file-alt"></i></a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('profesionales.edit', $item->id) }}"><i class="fa fa-fw fa-edit"></i></a>
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Delete Médico"
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
                @if(!empty($profesionales))
                {!! $profesionales->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
            @endif
            </div>
        </div>
    </div>
    <script src="{{ asset('js/util.js') }}"></script>

@endsection
