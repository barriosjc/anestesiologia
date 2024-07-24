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
                <form id="form_consumo" action="{{ route('consumos.guardar') }}" method="POST">
                    @csrf
                    <input type="hidden" name="parte_cab_id" id="parte_cab_id"
                        value="{{ old('parte_cab_id', $parte_cab_id) }}">
                    <input type="hidden" name="valor_orig" id="valor_orig">
                    <input type="hidden" name="valor_total" id="valor_total">
                    <div class="row gx-3 mb-3">
                        <div class="col-md-4">
                            <label for="codigo">Procedimiento</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="codigo" name="codigo"
                                    style="flex: 0 0 30%;" data-bs-toggle="tooltip"
                                    title="Debe ingresar un código de nomenclador con o sin guiones." placeholder="código">
                                <input type="text" class="form-control" id="descripcion" name="descripcion"
                                    data-bs-toggle="tooltip"
                                    title="Debe ingresar una descripción de práctica del nomenclador."
                                    placeholder="descripción">
                                <button type="button" id="search" class="btn btn-primary"><i
                                        class="fa fa-fw fa-search"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <labe for="">Nomenclador</label>
                                <select name="nomenclador_id" class="form-select nomenclador_id" id="nomenclador_id">
                                    {{-- los items por js --}}
                                </select>
                        </div>
                        <div class="col-md-1">
                            <label for="archivo">% </label>
                            <input type="text" class="form-control" id="porcentaje" name="porcentaje" required
                                data-bs-toggle="tooltip" title="Debe ingresar un valor numérico." value=100>
                        </div>
                        <div class="col-md-2">
                            <label for="archivo">Valor ($)</label>
                            <label class="form-control bg-light text-muted" id="total" name="total"></label>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-primary btn-lg" id="submitButton">
                                <i class="fa fa-fw fa-save"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        //submit para guardar los datos
        document.getElementById('submitButton').addEventListener('click', function() {
            document.getElementById('form_consumo').submit();
        });

        document.addEventListener('DOMContentLoaded', function() {
            let token = document.querySelector('input[name="_token"]').value;
            let parte_cab_id = document.getElementById('parte_cab_id').value;

            document.getElementById('search').addEventListener('click', function() {
                let codigo = document.getElementById('codigo').value;
                let descripcion = document.getElementById('descripcion').value;

                fetch('{{ route('nomenclador.valores.buscar') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            codigo: codigo,
                            descripcion: descripcion
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        let nomencladorSelect = document.getElementById('nomenclador_id');
                        nomencladorSelect.innerHTML = '';
                        const count = data.length;

                        // Handle the options in the select
                        if (count > 1) {
                            nomencladorSelect.innerHTML =
                                '<option value="">-- Seleccione una --</option>';
                        }

                        data.forEach(item => {
                            let option = document.createElement('option');
                            option.value = item.id;
                            option.text =
                                `${item.nivel} / ${item.codigo} / ${item.descripcion}`;
                            nomencladorSelect.appendChild(option);
                        });

                        // If there's only one result, make another AJAX request
                        if (count === 1) {
                            let nivel = data[0].nivel;
                            let porcentaje = parseFloat(document.getElementById('porcentaje').value);

                            // Fetch the value for the level
                            return fetch('{{ route('consumos.valor.buscar') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': token
                                    },
                                    body: JSON.stringify({
                                        nivel: nivel,
                                        parte_cab_id: parte_cab_id
                                    })
                                })
                                .then(response => response.json()) // Convert response to JSON
                                .then(valueData => {
                                    let totalValue = valueData.valor * (porcentaje / 100);
                                    document.getElementById('valor_orig').value = valueData.valor;
                                    document.getElementById('total').textContent = totalValue
                                        .toFixed(2);
                                    document.getElementById('valor_total').value = totalValue
                                        .toFixed(2);

                                });
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });

            // Add event listeners to clear inputs on focus
            const clearInput = function() {
                codigo.value = '';
                descripcion.value = '';
            };

            codigo.addEventListener('focus', clearInput);
            descripcion.addEventListener('focus', clearInput);

            //validar que no se pueda ingredar un porcentaje > 100
            let porcentajeInput = document.getElementById('porcentaje');
            let porcentajeValue = parseFloat(porcentajeInput.value);

            if (porcentajeValue > 100) {
                porcentajeInput.value = 100;
            }

            //modifica total si cambia porcentaje
            document.getElementById('porcentaje').addEventListener('input', function() {
                let porcentajeInput = document.getElementById('porcentaje');
                let porcentajeValue = parseFloat(porcentajeInput.value);
                let valorOrig = parseFloat(document.getElementById('valor_orig').value);

                if (porcentajeValue > 100) {
                    porcentajeInput.value = 100;
                    porcentajeValue = 100;
                }

                let totalValue = valorOrig * (porcentajeValue / 100);
                document.getElementById('total').textContent = totalValue.toFixed(2);
                document.getElementById('valor_total').value = totalValue.toFixed(2);

            });


            // Evento al cambiar el select
            document.getElementById('nomenclador_id').addEventListener('change', function() {
                let selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    fetch('{{ route('consumos.valor.buscar') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                nivel: selectedOption.text.split(' / ')[0],
                                parte_cab_id: parte_cab_id
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            let porcentaje = parseFloat(document.getElementById('porcentaje').value);
                            let valorOrig = parseFloat(data.valor); // Assuming `data.valor` contains the value
                            document.getElementById('valor_orig').value = valorOrig;
                            let total = valorOrig * (porcentaje / 100);
                            document.getElementById('total').textContent = total.toFixed(2);
                            document.getElementById('valor_total').value = total.toFixed(2);
                        })
                        .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>
@endsection
