@extends('layouts.main')

@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Nomenclador de prácticas y estudios') }}
                            </span>

                            <div class="float-right">
                                <a href="{{ route('nom_padres.index', $tipo) }}" title="Volver">
                                    <button class="btn btn-warning btn-sm float-right">
                                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                                    </button>
                                </a>
                                <a href="{{ route('nom_practicas_estudios.create', $nom_padre_id) }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        <th>Padre</th>
										<th>Código</th>
										<th>descripción</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nom_practicas_estudios as $item)
                                        <tr>
                                            <td>{{ $item->nom_padre_id }}</td>
											<td>{{ $item->codigo }}</td>
                                            <td>{{ $item->nombre }}</td>
                                            <td>
                                                <form id="delete-form-{{ $item->id }}" action="{{ route('nom_practicas_estudios.destroy',$item->id) }}" method="POST">
                                                    {{-- <a class="btn btn-sm btn-primary " href="{{ route('nom_practicas_estudios.show',$item->id) }}"><i class="fa fa-fw fa-eye"></i></a> --}}
                                                    <a class="btn btn-sm btn-success" href="{{ route('nom_practicas_estudios.edit',$item->id) }}"><i class="fa fa-fw fa-edit"></i></a>
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
                @if(!empty($nom_practicas_estudios))
                    {!! $nom_practicas_estudios->links() !!}
                @endif
            </div>
        </div>
    </div>
    {{-- <script>
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
    </script> --}}

@endsection
