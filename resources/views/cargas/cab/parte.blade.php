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
                                        <th style="width: 7%">Nro</th>
                                        <th>Centro</th>
										<th>Profesional</th>
										<th>Paciente</th>
										<th>Fecha</th>
                                        <th>Cobertura</th>
                                        <th>Estado</th>
                                        <th style="width: 5%"><i class="fa-solid fa-paperclip"></i></th>
                                        <th style="width: 16%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($partes as $item)
                                        <tr>
                                            <td>
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" 
                                                    @if(!empty($item->name))
                                                        data-bs-title="usuario: {{$item->name}} - cargado: {{$item->created_at}}"
                                                    @endif
                                                    class="badge bg-primary">{{ $item->id }}
                                                </span>
                                            </td>
											<td>{{ $item->centro }}</td>
                                            <td>{{ $item->profesional }}</td>
											<td>{{ $item->paciente }}</td>
											<td>{{ $item->fec_prestacion }}</td>
                                            <td>{{ $item->sigla }}</td>
                                            <td>
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" 
                                                    @if(!empty($item->observacion))
                                                        data-bs-title="{{$item->observacion}}"
                                                    @endif
                                                    class="badge bg-{{ $item->est_id == 1 ? 'primary' : 
                                                                        ($item->est_id == 2 ? 'danger' : 
                                                                        ($item->est_id == 3 ? 'warning' :
                                                                        ($item->est_id == 4 ? 'info' :
                                                                        ($item->est_id == 5 ? 'secondary' :
                                                                        'success')))) }}">{{ $item->est_descripcion }}
                                                    @if(!empty($item->observacion))
                                                        <span class="badge text-bg-dark"> </span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td>{{ $item->cantidad }}</td>
                                            <td class="td-actions">
                                                <a class="btn btn-sm btn-info llama_modal" data-bs-toggle="modal" 
                                                    data-bs-target="#valorModal" data-id="{{ $item->id}}"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" 
                                                    data-bs-title="Cargado todo el parte, ahora para pasar: A liquidar, click aquí.">
                                                    <i class="fa-solid fa-rotate-right"></i>
                                                </a>
                                                <a class="btn btn-sm btn-success" href="{{ route('partes_cab.edit', $item->id) }}">
                                                    <i class="fa fa-fw fa-edit"></i>
                                                </a>
                                                <form id="delete-form-{{ $item->id }}" action="{{ route('partes_cab.destroy', $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Delete parte"
                                                        onclick="confirmDelete({{ $item->id }})">
                                                        <i class="far fa-trash-alt text-white"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if(empty($partes))
                                        <tr>
                                            <td colspan="9" class="text-center">No hay cargados partes hasta el momento.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {{-- {!! $partes->links() !!} --}}
            </div>
        </div>
    </div>

    {{-- modales --}}
    <div class="modal fade" id="valorModal" tabindex="-1" aria-labelledby="valorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="valorModalLabel">Pasar el parte a: A liquidar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('consumos.aprocesar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <label class="label-control">Observaciones</label>
                        <textarea rows="4" name="observaciones" class="form-control"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{asset('js/util.js')}}"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script>
        $(document).ready( function () {
            $('[data-bs-toggle="tooltip"]').tooltip({
                html: true 
            });
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

            //inicializa el tooltip, para no entrar en conflicto en la grilla
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-title]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
            // carga el id del parte_cab cuando hace click para abrir el modal
            $('.llama_modal').on('click', function() {
                var parteCabId = $(this).data('id');
                console.log("pasa por aca");
                document.querySelector('input[type="hidden"][name="id"]').value = parteCabId;
            });
    </script>

@endsection
