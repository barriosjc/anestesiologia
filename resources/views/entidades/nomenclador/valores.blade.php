@extends('layouts.main')

@section('template_title')
    Valorización del nomenclador
@endsection

@section('contenido')
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.css" /> --}}
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Valorización') }}
                            </span>
                            <div class="form-group float-right">
                                <div class="btn btn-sm btn-primary llama_modal float-right" data-bs-toggle="modal"
                                    data-bs-target="#nuevoModal">
                                    <span>{{ __('Nuevo') }}</span>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="reportForm" action="{{ route('nomenclador.valores.filtrar') }}" method='GET'>
                            {{-- @csrf --}}
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="codigo">Coberturas</label>
                                    <select class="form-select" id="grupo" name="grupo">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($coberturas as $item)
                                            <option value="{{ $item->grupo }}" {{$cobertura_id == $item->id ? 'selected' : ''}}>{{ $item->sigla.' / '.$item->grupo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="periodo">Periodo</label>
                                    <select class="form-select" id="periodo" name="periodo">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($periodos as $item)
                                            <option value="{{ $item->nombre }}" {{$periodo == $item->nombre ? 'selected' : ''}}>{{ $item->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3 d-flex align-items-end">
                                    <button id="submitInputs" class="btn btn-primary" type="submit">Cargar
                                        Nomenclador</button>
                                </div>
                            </div>
                        </form>


                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="table_data">
                                <thead class="thead">
                                    <tr>
                                        <th>Nro</th>
                                        <th>Grupo</th>
                                        <th>Periodo</th>
                                        <th>Nivel</th>
                                        <th>Tipo</th>
                                        <th>Valor</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($valores as $item)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td>{{ $item->grupo }}</td>
                                            <td>{{ $item->periodo }}</td>
                                            <td>{{ $item->nivel }}</td>
                                            <td><span
                                                    class="badge bg-{{ $item->tipo == 1 ? 'primary' : 'success' }}">{{ $item->tipo == 1 ? 'Manual' : 'Factor' }}</span>
                                            </td>
                                            <td>
                                                <!-- Modal -->
                                                <div class="btn btn-warning btn-small llama_modal" data-bs-toggle="modal"
                                                    data-bs-target="#valorModal" data-id="{{ $item->id }}"
                                                    data-valor="{{ $item->valor }}">
                                                    <i class="fa-regular fa-pen-to-square icon-small"></i>
                                                </div><span> $ {{ number_format((float) $item->valor, 2, ',', '.')  }}</span>
                                            </td>
                                            <td class="td-actions">
                                                <form id="delete-form-{{ $item->id }}"
                                                    action="{{ route('nomenclador.valores.borrar', $item->id) }}" method="POST">
                                                    @csrf
                                                    @if (empty($item->deleted_at))
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="confirmDelete({{ $item->id }})">
                                                            <i class="far fa-trash-alt text-white"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-success btn-sm"
                                                            title="Recover data" data-bs-toggle="tooltip">
                                                            <i class="fa-solid fa-rotate-left"></i></button>
                                                    @endif
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if (!empty($valores))
                    {!! $valores->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif
            </div>
        </div>
    </div>
    <script src="{{ asset('js/util.js') }}"></script>
    {{-- <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script> --}}

    <script>
        $(document).ready(function() {
            // Maneja el clic en el botón que abre el modal
            $('.llama_modal').on('click', function() {
                // Obtiene los datos del botón
                var id = $(this).data('id');
                var valor = $(this).data('valor');

                // Asigna los datos al campo hidden y al input del modal
                $('#valorModal input[name="valores_id"]').val(id);
                $('#valorModal input[name="valor"]').val(valor);
            });
        });
    </script>

    {{-- modales --}}
    <div class="modal fade" id="valorModal" tabindex="-1" aria-labelledby="valorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="valorModalLabel">Valor en $ o unidades</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('nomenclador.valor.guardar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="valores_id" value="">
                        <label class="label-control">Valor</label>
                        <input type="text" name="valor" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- modales nuevo --}}
    <div class="modal fade" id="nuevoModal" tabindex="-1" aria-labelledby="nuevoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo, puede copiar o cargar valores desde cero</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('nomenclador.valores.nuevo') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="cobertura_id">Coberturas</label>
                                <select class="form-select"  name="cobertura_id">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($coberturas as $item)
                                        <option value="{{ $item->id }}">{{ $item->sigla }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="periodo">Periodo</label>
                                <select class="form-select" name="periodo">
                              <option value="">-- Seleccione --</option>
                                    @foreach ($periodos as $item)
                                        <option value="{{ $item->nombre }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <h4>Copiar desde</h4>
                            <div class="form-group col-md-6">
                                <label for="cobertura_id">Coberturas</label>
                                <select class="form-select" name="cobertura_id_copy">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($coberturas as $item)
                                        <option value="{{ $item->id }}">{{ $item->sigla }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="centro_id">Periodo</label>
                                <select class="form-select" name="periodo_copy">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($periodos as $item)
                                        <option value="{{ $item->nombre }}">{{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
