@extends('layouts.main')

@section('template_title')
    Médicos
@endsection

@section('contenido')
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
                            <table class="table table-striped table-hover">
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
                                            <td>{{ ++$i }}</td>
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
                @if(!empty($coberturas))
                    {!! $coberturas->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif
            </div>
        </div>
    </div>
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: '¿Confirma eliminar?',
                text: "No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-'+id).submit();
                }
            })
        }
    </script>

@endsection
