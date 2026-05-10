@extends('layouts.main')

@section('template_title')
    Valorización del nomenclador
@endsection

@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header py-2">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Valorización') }}
                            </span>
                            <div class="form-group float-right">
                                <a href="{{ request('back', url()->previous()) }}" title="Volver">
                                    <button class="btn btn-warning btn-sm float-right">
                                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                                    </button>
                                </a>
                                <div class="btn btn-sm btn-success float-right" data-bs-toggle="modal"
                                    data-bs-target="#nuevoModal" data-toggle="tooltip"
                                    title="Copiar de una lista de precios existente y crea una nueva Lista de precios con el grupo ingresado, este nuevo no debe existir."
                                    data-bs-toggle="tooltip">
                                    <span>{{ __('Copiar') }}</span>
                                </div>
                                <a href="{{ route('nomenclador.valor.nuevo') }}" class="btn btn-primary btn-sm float-right"
                                    data-placement="left">
                                    {{ __('Nuevo') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="reportForm" action="{{ route('nomenclador.valores.filtrar') }}" method="GET">
                            <div class="row align-items-end">

                                <div class="col-md-3">
                                    <label class="form-label small" for="grupo">Grupo</label>
                                    <input class="form-control" id="grupo" name="grupo" type="text"
                                        placeholder="grupo" value="{{ old('grupo') }}" />
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small" for="nivel">Nivel</label>
                                    <select class="form-select" id="nivel" name="nivel">
                                        <option value="">-- Seleccione --</option>
                                        @foreach ($niveles as $item)
                                            <option value="{{ $item->nivel }}"
                                                {{ $nivel == $item->nivel ? 'selected' : '' }}>
                                                {{ $item->nivel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <button id="submitInputs" class="btn btn-primary">
                                        Filtrar Listas
                                    </button>
                                </div>

                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="table_data">
                                <thead class="thead">
                                    <tr>
                                        <th>Nro</th>
                                        <th>Grupo</th>
                                        <th>Nivel</th>
                                        <th>Tipo</th>
                                        <th>Valor</th>
                                        <th>Moneda
                                    </tr>
                                    <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($valores as $item)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td>{{ $item->grupo }}</td>
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
                                                </div><span> $
                                                    {{ number_format((float) $item->valor, 2, ',', '.') }}</span>
                                            </td>
                                            <td>{{ $item->moneda }}</td>
                                            <td class="td-actions">
                                                @if (empty($item->deleted_at))
                                                    <form id="delete-form-{{ $item->id }}"
                                                        action="{{ route('nomenclador.valor.borrar', $item->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        {{-- @method('DELETE')  se cambia la ruta a post --}}
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="confirmDelete({{ $item->id }})">
                                                            <i class="far fa-trash-alt text-white"></i></button>
                                                    </form>
                                                @else
                                                    <form id="restore_form_{{ $item->id }}"
                                                        action="{{ route('nomenclador.valor.borrar', $item->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button type="button" class="btn btn-success btn-sm"
                                                            onclick="confirmJob('¿Desea restaurar este registro?', 'restore_form_{{ $item->id }}')">
                                                            <i class="fa-solid fa-rotate-left"></i></button>
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
                @if (!empty($valores))
                    {!! $valores->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif
            </div>
        </div>
    </div>


    {{-- modales --}}
    <div class="modal fade" id="valorModal" tabindex="-1" aria-labelledby="valorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="valorModalLabel">$ o unidades, formato $: 1.200,50</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('nomenclador.valor.precio.guardar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <input type="hidden" name="hidden_valor_id">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">$</span>
                            </div>
                            <input type="text" class="form-control" placeholder="Valor" name="valor"
                                aria-label="Valor" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
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
                    <h5 class="modal-title">Nueva Lista</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('nomenclador.valores.grupo.guardar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="small mb-1" for="grupo_c">Grupo a copiar</label>
                                <input class="form-control" id="grupo_c" name="grupo_c" type="text"
                                    placeholder="grupo_c" value="{{ old('grupo_c') }}" />
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1" for="grupo_n">Nuevo Grupo</label>
                                <input class="form-control" id="grupo_n" name="grupo_n" type="text"
                                    placeholder="grupo_n" value="{{ old('grupo_n') }}" />
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1" for="porcentaje">% incremento</label>
                                <input class="form-control" id="porcentaje" name="porcentaje" type="text"
                                    placeholder="porcentaje" title="Valor % permitido de -99 a 100"
                                    data-bs-toggle="tooltip" value="{{ old('porcentaje') }}" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-sm btn-primary">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        $(document).ready(function() {
            // Maneja el clic en el botón que abre el modal
            $('.llama_modal').on('click', function() {
                var id = $(this).data('id');
                var valor = $(this).data('valor');

                console.log("ID recibido:", id);

                // Cargar en el hidden correcto
                $('#valorModal input[name="hidden_valor_id"]').val(id);

                // Asigna los datos al campo hidden y al input del modal
                $('#valorModal input[name="id"]').val(id);
                let valorFormateado = parseFloat(valor).toLocaleString('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                $('#valorModal input[name="valor"]').val(valorFormateado);
            });
        });
    </script>
@endpush
