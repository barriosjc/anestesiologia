<form id="form_presupuesto" method="POST" action="{{ route('presupuestos.det.store', $presupuestosCab->id) }}"
    role="form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="gerenciadora_id" id="gerenciadora_id" value="{{$presupuestosCab->gerenciadora_id}}">
    <input type="hidden" name="centro_id" id="centro_id" value="{{$presupuestosCab->centro_id}}">
    <input type="hidden" name="valor_orig" id="valor_orig">
    <input type="hidden" name="valor_total" id="valor_total">
    <div class="card mt-3 p-3 border">
        <div class="card-body">
            <input type="hidden" name="presupuestocabid" value="{{ $presupuestosCab->id }}">
            <div class="row gx-3 mb-3">
                <div class="col-md-6">
                    <label class="small mb-1">Coberturas</label>
                    <select name="cobertura_id" class="select2 form-select form-select-2" id="cobertura_id"
                        data-bs-placement="top"
                        data-bs-title="Si el paciente se realiaza alguno de los procedimientos bajo una cobertura indíquela, o seleccione 'Particular'.">
                        <option value="">-- Seleccione --</option>
                        @foreach ($coberturas as $data)
                            <option value="{{ $data->id }}">
                                {{ $data->sigla }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row gx-3 mb-3">
                <div class="col-md-2">
                    <label class="small mb-1" for="periodo">Periodo</label>
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
                        <input type="text" class="form-control" id="codigo" name="codigo" style="flex: 0 0 30%;"
                            data-bs-toggle="tooltip" title="Debe ingresar un código de nomenclador con o sin guiones."
                            placeholder="código">
                        <input type="text" class="form-control" id="descripcion" name="descripcion"
                            data-bs-toggle="tooltip" title="Debe ingresar una descripción de práctica del nomenclador."
                            placeholder="descripción">
                        <button type="button" id="search" class="btn btn-primary"><i
                                class="fa fa-fw fa-search"></i></button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="small mb-1">Nomenclador</label>
                    <select name="nomenclador_id" class="form-select nomenclador_id" id="nomenclador_id">
                        {{-- los items por js --}}
                    </select>
                </div>
                <div class="col-md-1 pt-3">
                    <label class="small mb-1" for="archivo">% </label>
                    <input type="text" class="form-control" id="porcentaje" name="porcentaje"
                        data-bs-toggle="tooltip" title="Debe ingresar un valor numérico." value=100>
                </div>
                <div class="col-md-2 pt-3">
                    <label class="small mb-1" for="archivo">Valor ($)</label>
                    <label class="form-control bg-light text-muted" id="total" name="total">0,00</label>
                </div>
            </div>
            <div class="row gx-3 mb-3">
                <div class="col-md-12">
                    <label class="small mb-1" for="observaciones">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                        placeholder="Se recomienda no ingresar prácticas a realizar aquí, solo observaciones."></textarea>
                </div>
            </div>
            <div class="box-footer mt20">
                <button type="submit" id="submitButton" class="btn btn-primary">{{ __('Guardar') }}</button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
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
            const gerenciadora_id = document.getElementById('gerenciadora_id').value;
            const cobertura_id = document.getElementById('cobertura_id').value;

            if (codigo == "" && descripcion == "") {
                return
            }
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
                        gerenciadora_id: gerenciadora_id,
                        cobertura_id: cobertura_id   
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
            const gerenciadora_id = document.getElementById('gerenciadora_id').value;
            const cobertura_id = document.getElementById('cobertura_id').value;
            const centro_id = document.getElementById('centro_id').value;
            const periodo = document.getElementById('periodo').value;
            let porcentajeInput = document.getElementById('porcentaje');
            let porcentajeIni = parseFloat(porcentajeInput.value);
            let nomencladorSelect = document.getElementById('nomenclador_id');
            if (periodo == "" || nomencladorSelect.options.length == 0) {
                return
            }
            selectedNomencladorId = nomencladorSelect.options[nomencladorSelect.selectedIndex];
            nomenclador_id = selectedNomencladorId.value //modifique para guardar el nivel o codigo, con este valor busco $
            nivel = selectedNomencladorId.getAttribute('data-nivel');

            return fetch('{{ route('nomenclador.valores.traer.uno') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    periodo: periodo, 
                    nivel: nivel,
                    gerenciadora_id: gerenciadora_id,
                    cobertura_id: cobertura_id,
                    centro_id: centro_id
                })
            })
            .then(response => {
                if (!response.ok) {
                    // Si no es exitoso, manejar error
                    return response.json().then(errorData => {
                        // console.error(errorData.error);
                        limpiarCampos(); // Llama a la función para limpiar los campos
                        throw new Error(errorData.error);
                    });
                }
                return response.json(); // Convertir respuesta a JSON
            })
            .then(valueData => {
                porcentajeInput.value = 100;
                let totalValue = valueData ;
                let totalView = totalValue.toFixed(2);
                totalView = totalView.replace('.', ',');
                totalView = totalView.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                document.getElementById('valor_orig').value = valueData;
                document.getElementById('total').textContent = totalView;
                document.getElementById('valor_total').value = totalValue.toFixed(2);
            })
            .catch(error => {
                console.error('Error en la solicitud:', error);
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
