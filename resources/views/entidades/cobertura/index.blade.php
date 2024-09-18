@extends('layouts.main')

@section('template_title')
    Médicos
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
                                {{ __('Coberturas') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('coberturas.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
										<th>Nombre</th>
                                        <th>Sigla</th>
										<th>CUIT</th>
                                        <th>Grupo</th>
                                        <th>Edad desde</th>
                                        <th>Edad hasta</th>
                                        <th>% adic.</th>
                                        <th style="width: 15%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($coberturas as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
											<td>{{ $item->nombre }}</td>
                                            <td>{{ $item->sigla }}</td>
											<td>{{ $item->cuit }}</td>
                                            <td>{{ $item->grupo }}</td>
                                            <td>{{ $item->edad_desde }}</td>
                                            <td>{{ $item->edad_hasta }}</td>
                                            <td>{{ $item->porcentaje_adic }}</td>
                                            <td>
                                                <form id="delete-form-{{ $item->id }}" action="{{ route('coberturas.destroy',$item->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('coberturas.show',$item->id) }}"><i class="fa fa-fw fa-eye"></i></a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('coberturas.edit',$item->id) }}"><i class="fa fa-fw fa-edit"></i></a>
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Delete Usuario"
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
                {{-- @if(!empty($coberturas))
                    {!! $coberturas->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif --}}
            </div>
        </div>
    </div>
    <script src="{{asset('js/util.js')}}"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script>
        $(document).ready( function () {
            //iniciar tabla enrriquesida
            $('#table_data').DataTable(
                {
                    "columnDefs":[{
                        "targets":[8],
                        "orderable":false
                        }
                    ],
                    "order": [] 
                }
            );
        });
    </script>

@endsection
