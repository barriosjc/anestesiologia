@extends('layouts.main')

@section('contenido')
    <section class="content container-fluid">  
        <div class="card card-default">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title">Detalle del parte</span>
                <div>
                    <a href="{{ route('consumos.partes.filtrar') }}" class="btn btn-info btn-sm" data-placement="left">
                        Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <input type="hidden" name="consumo_cab_id" id="consumo_cab_id" value="{{ old('consumo_cab_id', $consumo_cab_id) }}">

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tabla_data">
                        <thead class="thead">
                            <tr>
                                <th>Nro hoja</th>
                                <th>Documento</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($partes_det as $item)
                                <tr>
                                    <td>{{ $item->nro_hoja }}</td>
                                    <td>{{ $item->documento->nombre }}</td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-warning"
                                            href="{{ route('partes_det.download', $item->id) }}"><i
                                                class="fa fa-fw fa-download"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <hr>
                <h4>Carga de consumo</h4>
                <form action="{{ route('partes_det.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="consumo_cab_id" id="consumo_cab_id" value="{{ old('consumo_cab_id', $consumo_cab_id) }}">
                    <div class="row gx-3 mb-3">
                        <div class="col-md-5">
                            <label class="small mb-1">Nomenclador</label>
                            <select name="nomenclador_id" class="form-select nomenclador_id" id="nomenclador_id">
                                <option value="">-- Seleccione --</option>
                                @foreach ($nomenclador as $data)
                                    <option value="{{ $data->id }}">
                                        {{$data->nivel." / ".$data->codigo." / ".$data->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label for="archivo">% </label>
                            <input type="text" class="form-control" id="porcentaje" name="porcentaje" required
                                data-bs-toggle="tooltip" title="Debe ingresar un valor numérico." value=100 >
                        </div>
                        <div class="col-md-2">
                            <label for="archivo">Valor ($)</label>
                            <label class="form-control form-control-label" id="total" name="total">100</label>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">{{ __('Guardar') }}</button>
                        </div>
                    </div>
                </form>                
            </div>
        </div>
    </section>

    <!-- Incluye el archivo JS compilado -->
    <script src="{{ mix('js/app.js') }}"></script>

    <script>
        $(document).ready(function() {
            const element = document.querySelector('.nomenclador_id');
            const choices = new Choices(element, {
                    maxItemCount: 10, // Ajusta esto al número deseado de elementos visibles
                });
            
            $('#nomenclador_id').on('change', function() {
                var nomencladorId = $(this).val();
                var consumo_cab_id = $('#consumo_cab_id').val();
console.log(consumo_cab_id)
                if (nomencladorId) {
                    $.ajax({
                        url: '/consumos/valor',
                        type: 'GET',
                        data: { id: nomencladorId,
                                consumo_cab_id: consumo_cab_id
                         },
                        success: function(response) {
                            $('#total').text(response.valor);
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                        }
                    });
                } else {
                    $('#total').text('0');
                }
            });
        });
    </script>
@endsection
