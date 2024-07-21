@extends('layouts.main')

@section('template_title')
    Rendiciones
@endsection

@section('contenido')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.css" />
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Cargas') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('partes_cab.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
                                  {{ __('Nuevo') }}
                                </a>
                              </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="table_data">
                                <thead class="thead">
                                    <tr>
                                        <th>Nro</th>
                                        <th>Centro</th>
										<th>Profesional</th>
										<th>Paciente</th>
										<th>Fecha</th>
                                        <th>Cobertura</th>
                                        <th>Estado</th>
                                        <th>Docs</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($partes as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
											<td>{{ $item->centro }}</td>
                                            <td>{{ $item->profesional }}</td>
											<td>{{ $item->paciente }}</td>
											<td>{{ $item->fec_prestacion }}</td>
                                            <td>{{ $item->cobertura }}</td>
                                            <td><span class="badge bg-{{($item->est_id == 1 ? 'primary' : ($item->est_id == 2 ? 'danger' : 'success'))}}">{{ $item->est_descripcion }}</span></td>
                                            <td>{{ $item->cantidad }}</td>
                                            <td class="td-actions">
                                                <form id="delete-form-{{ $item->id }}" action="{{ route('partes_cab.destroy',$item->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-success" href="{{ route('partes_cab.edit',$item->id) }}"><i class="fa fa-fw fa-edit"></i></a>
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Delete parte"
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
                {{-- {!! $partes->links() !!} --}}
            </div>
        </div>
    </div>
    <script src="{{asset('js/util.js')}}"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script>

    $(document).ready( function () {
        $('#table_data').DataTable(
            {
                "columnDefs":[{
                    "targets":[7],
                    "orderable":false
                    }
                ]
            }
        );
    });
    </script>

@endsection
