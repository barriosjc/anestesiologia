@extends('layouts.main')

@section('template_title')
    Rendiciones
@endsection

@section('contenido')
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
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>Nro</th>
                                        <th>Centro</th>
										<th>Profesional</th>
										<th>Paciente</th>
										<th>Fecha</th>
                                        <th>Cobertura</th>
                                        <th>Docs</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($partes as $item)
                                        <tr>
                                            <td>{{ ++$i }}</td>
											<td>{{ $item->centro }}</td>
                                            <td>{{ $item->profesional }}</td>
											<td>{{ $item->paciente }}</td>
											<td>{{ $item->fec_prestacion }}</td>
                                            <td>{{ $item->cobertura }}</td>
                                            <td>{{ $item->cantidad }}</td>
                                            <td>
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
                {!! $partes->links() !!}
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
