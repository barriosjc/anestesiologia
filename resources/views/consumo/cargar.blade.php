@extends('layouts.main')

@section('contenido')
    <section class="content container-fluid">
        <div class="card card-default">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title">Detalle del parte nro: {{$parte_cab_id}}</span>
                <div>
                    <!-- Modal -->
                    <div class="btn btn-warning btn-sm llama_modal" data-bs-toggle="modal"
                                data-bs-target="#valorModal" data-id="{{ $parte_cab_id}}">
                        Observar el parte
                    </div>
                    <a href="{{ route('consumos.partes.filtrar') }}" class="btn btn-info btn-sm" data-placement="left">
                        Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($soloConsulta)
                    <div class="alert alert-danger" role="alert">
                        Parte en modo consulta, solo se puede editar partes en estados: A liquidar o En facturación.
                    </div>
                @endif
                <div class="alert alert-info" role="alert">
                    {{ "{$data->sigla} / {$data->centro} / {$data->profesional} / {$data->paciente} ({$data->edad}) / 
                        {$data->fec_prestacion} / Obs: {$observaciones}" }}
                  </div>
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
                                <tr style="height: 30px;">
                                    <td style="padding: 5px;">{{ $item->nro_hoja }}</td>
                                    <td style="padding: 5px;">{{ $item->documento->nombre }}</td>
                                    <td class="text-end" style="padding: 5px;">
                                        <a class="btn btn-sm btn-warning" target="_blank" 
                                                href="{{ route('partes_det.download', $item->id) }}">
                                            <i class="fa fa-fw fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            @if(empty($partes_det))
                                <tr style="height: 30px;">
                                    <td colspan="3" class="text-center" style="padding: 5px;">No hay documentación cargada hasta el momento.</td>
                                </tr>
                            @endif
                        </tbody>                        
                    </table>
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tabla_consumo">
                        <thead class="thead">
                            <tr>
                                <th>Practica</th>
                                <th>%</th>
                                <th>Valor ($)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($consumos as $item)
                                <tr>
                                    <td>{{ $item->nivel ." / ".$item->codigo ." / ".$item->nom_descripcion  }}</td>
                                    <td>{{ $item->porcentaje }}</td>
                                    <td>{{ number_format((float) $item->valor, 2, ',', '.') }}</td>
                                    <td class="text-end">
                                    <form id="delete-form-{{ $item->id }}" 
                                            action="{{ route('consumos.borrar', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <a class="btn btn-sm btn-danger"
                                                onclick="confirmDelete({{ $item->id }})"><i
                                                class="fa fa-fw fa-trash"></i></a>
                                    </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if(empty($consumos))
                            <tr colspan="4" class="text-center">No hay cargados consumos hasta el momento.</tr>
                            @endif
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
                    <input type="hidden" name="nom_padre_json" id="nom_padre_json" value="{{ $nom_padre_json }}">
                    <input type="hidden" name="nom_padre_id" id="nom_padre_id">
                    <input type="hidden" name="gerenciadora_id" id="gerenciadora_id" value="{{ $data->gerenciadora_id }}">
                    <div class="row gx-3 mb-3">
                        <div class="col-md-2">
                            <label class="small mb-1" for="archivo">Periodo</label>
                            <select name="periodo" class="form-select periodo" id="periodo">
                                <option value="">-- Seleccione --</option>
                                @foreach ($periodos as $item)
                                    <option value="{{ $item->nombre }}">
                                        {{ $item->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small mb-1" for="codigo">Procedimiento</label>
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
                        <div class="col-md-6">
                            <label class="small mb-1" for="nomenclador_id">Nomenclador</label>
                                <select name="nomenclador_id" class="form-select nomenclador_id" id="nomenclador_id">
                                    {{-- los items por js --}}
                                </select>
                        </div>
                        <div class="col-md-1 pt-3">
                            <label class="small mb-1" for="porcentaje">% </label>
                            <input type="text" class="form-control" id="porcentaje" name="porcentaje" required
                                data-bs-toggle="tooltip" title="Debe ingresar un valor numérico." value=100>
                        </div>
                        <div class="col-md-2 pt-3">
                            <label class="small mb-1" for="total">Valor ($)</label>
                            <label class="form-control bg-light text-muted" id="total" name="total">0,00</label>
                        </div>


                        <div class="col-md-1 d-flex align-items-end">
                            @if(!$soloConsulta)
                            <button type="button" class="btn btn-primary btn-lg" id="submitButton">
                                <i class="fa fa-fw fa-save"></i></button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- modales --}}
        <div class="modal fade" id="valorModal" tabindex="-1" aria-labelledby="valorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="valorModalLabel">Pasar el parte a observado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('consumos.observar') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="id" value="{{$parte_cab_id}}">
                            <label class="label-control">Observaciones</label>
                            <textarea rows="4" name="observaciones" class="form-control"> {{ $observaciones }} </textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </section>

    @push('scripts')
        <script>
            //submit para guardar los datos
            // let nomenclador_id = null;
            // let parte_cab_id = null;
            var submitButton = document.getElementById('submitButton');
            if (submitButton) {        
                submitButton.addEventListener('click', function() {
                    const select = document.getElementById('nomenclador_id');
                    const selectedOption = select.options[select.selectedIndex];
                    const nomPadre = selectedOption.getAttribute('data-nom_padre');

                    document.getElementById('nom_padre_id').value = nomPadre;

                    document.getElementById('form_consumo').submit();
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                let token = document.querySelector('input[name="_token"]').value;
                
                // calcula el valor al cambiar el periodo
                document.getElementById('periodo').addEventListener('change', function() {
                    let selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value) {
                        mostrarValor();
                    }
                });

                // busca en el nomenclador, si es uno lo valoriza o carga el combo de practicas
                document.getElementById('search').addEventListener('click', function() {
                    let codigo = document.getElementById('codigo').value;
                    let descripcion = document.getElementById('descripcion').value;
                    const nom_padre_json = document.getElementById('nom_padre_json').value;
                    // const gerenciadora_id = document.getElementById('gerenciadora_id').value;

                    if (codigo == "" && descripcion == "") {
                        return
                    }
                    // console.log("cod:",codigo, "desc:",descripcion, "nom_padre_json:",nom_padre_json);
                    // busca en el nomenclador, puede traer uno o varios
                    fetch('{{ route('nomenclador.buscar.coddesc') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                codigo: codigo,
                                descripcion: descripcion,
                                nom_padre_json: nom_padre_json,   
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
                                // option.value = item.id;
                                option.value = item.id;
                                option.setAttribute('data-nom_padre', item.nom_padre_id);
                                option.setAttribute('data-nivel', item.nivel);
                                option.text =
                                    `${item.nivel !== null ? item.nivel + ' / ' : ''} ${item.codigo} / ${item.descripcion}`;
                                nomencladorSelect.appendChild(option);
                            });

                            if (count === 1) {
                                mostrarValor()
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

                //validar que no se pueda ingresar un porcentaje > 100
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

                    if (porcentajeValue > 200) {
                        porcentajeInput.value = 100;
                        porcentajeValue = 100;
                    }

                    let totalValue = valorOrig * (porcentajeValue / 100);
                    let totalView = totalValue.toFixed(2);
                    totalView = totalView.replace('.', ',');
                    totalView = totalView.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                    document.getElementById('total').textContent = totalView;
                    document.getElementById('valor_total').value = totalValue.toFixed(2);

                });


                // aca selecciono una practica del combo
                document.getElementById('nomenclador_id').addEventListener('change', function() {
                    let selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value) {
                        mostrarValor();
                    }
                });
                // ---------------------------------------------------------------------------------
                // funcion comun que se llama para mostrar el valor
                function mostrarValor() {
                    let parte_cab_id = document.getElementById('parte_cab_id').value;
                    let periodo = document.getElementById('periodo').value;
                    let porcentajeInput = document.getElementById('porcentaje');
                    let porcentajeIni = parseFloat(porcentajeInput.value);
                    let nomencladorSelect = document.getElementById('nomenclador_id');
                    
                    if (periodo == "" || nomencladorSelect.options.length == 0) {
                        return
                    }
                    
                    selectedNomencladorId = nomencladorSelect.options[nomencladorSelect.selectedIndex];
                    nomenclador_id = selectedNomencladorId.value
                    nivel = selectedNomencladorId.getAttribute('data-nivel');

                    console.log('=== INICIANDO BÚSQUEDA ===');
                    console.log('Periodo:', periodo);
                    console.log('Nivel:', nivel);
                    console.log('Parte CAB ID:', parte_cab_id);

                    return fetch('{{ route('consumos.valor.buscar') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json; charset=UTF-8',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            periodo: periodo, 
                            nivel: nivel,
                            parte_cab_id: parte_cab_id
                        })
                    })
                    .then(response => {
                        // console.log('Response status:', response.status);
                        // console.log('Response ok:', response.ok);
                        
                        if (!response.ok) {
                            return response.json().then(errorData => {
                                console.error('ERROR DATA:', errorData);
                                limpiarCampos();
                                throw new Error(errorData.error);
                            });
                        }
                        return response.json();
                    })
                    .then(valueData => {
                        // console.log('=== DATOS RECIBIDOS ===');
                        // console.log('Value Data completo:', valueData);
                        // console.log('Valor:', valueData.valor);
                        // console.log('Porcentaje:', valueData.porcentaje);
                        
                        let porcentaje = porcentajeIni + valueData.porcentaje;
                        porcentajeInput.value = porcentaje;
                        let totalValue = valueData.valor * (porcentaje / 100);
                        let totalView = totalValue.toFixed(2);
                        totalView = totalView.replace('.', ',');
                        totalView = totalView.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        
                        // console.log('Porcentaje final:', porcentaje);
                        // console.log('Total calculado:', totalValue);

                        document.getElementById('valor_orig').value = valueData.valor;
                        document.getElementById('total').textContent = totalView;
                        document.getElementById('valor_total').value = totalValue.toFixed(2);
                    })
                    .catch(error => {
                        console.error('=== ERROR CAPTURADO ===');
                        console.error('Error:', error);
                    });
                }
                // Función para limpiar los campos en caso de error
                function limpiarCampos() {
                    document.getElementById('valor_orig').value = '';
                    document.getElementById('total').textContent = '';
                    document.getElementById('valor_total').value = '';
                    document.getElementById('nom_padre_id').value = '';
                }

            });
        </script>
    @endpush
@endsection
