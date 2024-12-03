@extends('layouts.main')

@section('template_title')
    Rendiciones
@endsection

@section('contenido')
    {{-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> --}}

    <div class="container-fluid">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Cargas') }}
                            </span>

                            <div class="float-right">
                                <a href="{{ route('partes_cab.create') }}" class="btn btn-primary btn-sm float-right"
                                    data-placement="left">
                                    {{ __('Nuevo') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @include("cargas.cab.partials.filtro")

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
                                        <th style="width: 16%">(Partes: {{$partes->total()}})</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($partes as $item)
                                        <tr>
                                            <td>
                                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                    @if (!empty($item->name)) data-bs-title="usuario: {{ $item->name }} - cargado: {{ $item->created_at }}" @endif
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
                                                    @if (!empty($item->observacion)) data-bs-title="{{ $item->observacion }}" @endif
                                                    class="badge bg-{{ $item->est_id == 1
                                                        ? 'primary'
                                                        : ($item->est_id == 2
                                                            ? 'danger'
                                                            : ($item->est_id == 3
                                                                ? 'warning'
                                                                : ($item->est_id == 4
                                                                    ? 'info'
                                                                    : ($item->est_id == 5
                                                                        ? 'secondary'
                                                                        : 'success')))) }}">{{ $item->est_descripcion }}
                                                    @if (!empty($item->observacion))
                                                        <span class="badge text-bg-dark"> </span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td>{{ $item->cantidad }}</td>
                                            <td class="td-actions">
                                                @include('cargas.cab.partials.partes_acciones', [
                                                    'item' => $item,
                                                ])
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if (empty($partes))
                                        <tr>
                                            <td colspan="9" class="text-center">No hay cargados partes hasta el
                                                momento.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if (!empty($partes))
                    {!! $partes->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif
            </div>
        </div>
    </div>

    {{-- modales --}}
    @include('cargas.cab.partials.cambio_estado')

    <script src="{{ asset('js/util.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Inicializar tooltips
            $('[data-bs-toggle="tooltip"]').tooltip({
                html: true
            });

            $('.select2').select2({
                placeholder: "-- Seleccione --",
                allowClear: true
            });

            // Manejar la apertura del modal
            $(document).on('click', '.llama_modal', function() {
                var parteCabId = $(this).data('id');
                var observaciones = $(this).data('observaciones')
                document.querySelector('input[type="hidden"][name="id"]').value = parteCabId;
                document.querySelector('textarea[name="observaciones"]').value = observaciones;
            });
        });
    </script>
@endsection
