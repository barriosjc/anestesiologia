<div>
    {{-- si hace falta se deberia quitar y enviar el msg fuera el componete y mostrarlo donde esta actualmente --}}
    @include('utiles.alerts')

    {{-- hecho para livewire --}}
    <div class="container-fluid px-4">
        {{-- Wizard card example with navigation --}}
        <div class="card">
            <div class="card-header border-bottom">
                {{-- Wizard navigation --}}
                <div class="nav nav-pills nav-justified flex-column flex-xl-row nav-wizard" id="cardTab" role="tablist">
                    {{-- Wizard navigation item 1 --}}
                    <a class="nav-item nav-link {{ $currentTab == 1 ? 'active' : '' }}" id="wizard1-tab"
                        wire:click="selectTab(1)" data-bs-toggle="tab" role="tab" aria-controls="wizard1"
                        aria-selected="true">
                        <div class="wizard-step-icon">1</div>
                        <div class="wizard-step-text">
                            <div class="wizard-step-text-name">Votaciones</div>
                            <div class="wizard-step-text-details">ABM y listado </div>
                        </div>
                    </a>
                    {{-- Wizard navigation item 2 --}}
                    <a class="{{ empty($encuestas_id_selected) ? 'disabled-link' : '' }} nav-item nav-link {{ $currentTab == 2 ? 'active' : '' }}" id="wizard2-tab"
                        wire:click="selectTab(2)" data-bs-toggle="tab" role="tab" aria-controls="wizard2"
                        aria-selected="true">
                        <div class="wizard-step-icon">2</div>
                        <div class="wizard-step-text">
                            <div class="wizard-step-text-name">Periodos</div>
                            <div class="wizard-step-text-details">ABM y listado asociado a la encuesta</div>
                            <div class="wizard-step-text-details">{{ empty($cant_per) ? '' : 'cargado: ' . $cant_per }}
                            </div>
                        </div>
                    </a>
                    {{-- Wizard navigation item 3 --}}
                    <a class="{{ empty($encuestas_id_selected) ? 'disabled-link' : '' }} nav-item nav-link {{ $currentTab == 3 ? 'active' : '' }}" id="wizard3-tab"
                        wire:click="selectTab(3)" data-bs-toggle="tab" role="tab" aria-controls="wizard3"
                        aria-selected="true">
                        <div class="wizard-step-icon">3</div>
                        <div class="wizard-step-text">
                            <div class="wizard-step-text-name">Valores</div>
                            <div class="wizard-step-text-details">ABM y listado asociada a la encuesta</div>
                            <div class="wizard-step-text-details">{{ empty($cant_opc) ? '' : 'cargado: ' . $cant_opc }}
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content" id="cardTabContent">
                    {{-- Wizard tab pane item 1 --}}
                    <div class="tab-pane py-5 py-xl-3 fade {{ $currentTab == 1 ? 'show active' : '' }}" id="wizard1"
                        role="tabpanel" aria-labelledby="wizard1-tab">
                        <div class="row justify-content-center">
                            <div class="col-xxl-8 col-xl-10">
                                <h3 class="text-primary">Step 1 - Votaciones (Debe seleccionar una votación para que se habiliten los pasos 2 y 3)</h3>
                                {{-- <h5 class="card-title mb-4"></h5> --}}
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                {{-- <input type="hidden" name="empresas_id" value={{ $encuestas_id_selected }}> --}}
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <table class="table table-striped" id="tabla_encuestas"
                                                            name="tabla_encuestas">
                                                            <thead>
                                                                <tr>
                                                                    <th>Marca</th>
                                                                    <th>Empresa</th>
                                                                    <th>Nombre</th>
                                                                    <th>Edicion</th>
                                                                    <th>Op. x Col.</th>
                                                                    <th>Habilitado</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($encuestas as $encuesta)
                                                                    <tr>
                                                                        <td><input type="radio"
                                                                                class="form-check-input"
                                                                                name="encuestas_id" id="encuesta-id"
                                                                                wire:click="itemEncuestas_id({{ $encuesta->id }})">
                                                                        </td>
                                                                        </td>
                                                                        <td>{{ $encuesta->razon_social }}</td>
                                                                        <td>{{ $encuesta->encuesta }}</td>
                                                                        <td>{{ $encuesta->edicion }}</td>
                                                                        <td>{{ $encuesta->opcionesxcol }}</td>
                                                                        @if ($encuesta->habilitada == 1)
                                                                            <td>
                                                                                <div
                                                                                    class="badge bg-primary text-white rounded-pill-yes-no">
                                                                                    SI </div>
                                                                            </td>
                                                                        @else
                                                                            <td>
                                                                                <div
                                                                                    class="badge bg-danger text-white rounded-pill-yes-no">
                                                                                    NO</div>
                                                                            </td>
                                                                        @endif
                                                                        <td>
                                                                            <button
                                                                                class="btn btn-datatable btn-icon btn-transparent-dark me-2"
                                                                                wire:click="editar_encuesta({{ $encuesta->id }})">
                                                                                <i
                                                                                    class="fa-regular fa-pen-to-square"></i></button>
                                                                            <button type="button"
                                                                                wire:click="$emit('deleteencuesta', {{ $encuesta->id }})"
                                                                                title="Borrar encuesta"
                                                                                class="btn btn-datatable btn-icon btn-transparent-dark"><i
                                                                                    class="fa-regular fa-trash-can"></i></button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                        @if (!empty($encuestas))
                                                            {{ $encuestas->links() }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <h4>Cargue los datos de la encuesta</h4>
                                        <div class="form-group row">
                                            <label for="user_id_reconocido"
                                                class="col-sm-2 col-form-label">Empresa</label>
                                            <div class="col-sm-8">
                                                <select name="empresas_id" class="form-control" id="empresas_id"
                                                    wire:model="e_empresas_id">
                                                    <option value=""> --- Select ---</option>
                                                    @foreach ($empresas as $data)
                                                        <option value="{{ $data->id }}">
                                                            {{ $data->razon_social }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="encuesta" class="col-sm-2 col-form-label">Nombre</label>
                                            <div class="col-sm-4">
                                                <input type="text" class="form-control" id="encuesta"
                                                    name='encuesta' placeholder="encuesta" wire:model="e_encuesta">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="edicion" class="col-sm-2 col-form-label">Edición</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="edicion"
                                                    name="edicion" placeholder="edicion" wire:model="e_edicion">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="encuestaesxcol" class="col-sm-2 col-form-label">Cant. Valores por
                                                columna</label>
                                            <div class="col-sm-2">
                                                <input type="number" min="2" max="5"
                                                    class="form-control" id="opcionesxcol" name="opcionesxcol"
                                                    placeholder="opcionesxcol" wire:model="e_opcionesxcol">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="" class="col-sm-2 col-form-label"></label>
                                            <div class="col-sm-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                        id="habilitada" name="habilitada" checked
                                                        wire:model="e_habilitada">
                                                    <label class="form-check-label" for="habilitado">Habilitado
                                                        (No/Si)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div class="col-12">
                                            <div class="box-footer mt20">
                                                <button type="button" class="btn btn-primary"
                                                    wire:click="encuesta_store">{{ __('Guardar datos') }}</button>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <hr class="my-4" />
                                <div class="d-flex justify-content-between">
                                    @if ( !empty($encuestas_id_selected) )
                                        <button class="btn btn-light disabled" type="button" disabled>Previous</button>
                                        <button class="btn btn-primary" type="button"
                                            wire:click="selectTab(2)">Next</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Wizard tab pane item 2 --}}
                    <div class="tab-pane py-5 py-xl-3 fade {{ $currentTab == 2 ? 'show active' : '' }} "
                        id="wizard2" role="tabpanel" aria-labelledby="wizard2-tab">
                        <div class="row justify-content-center">
                            <div class="col-xxl-8 col-xl-10">
                                <h3 class="text-primary">Step 2 - Periodos</h3>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        {{-- <input type="hidden" name="encuestas_id" value={{ $encuestas_id }}> --}}
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <table class="table table-striped" id="tabla_periodos"
                                                    name="tabla_periodos">
                                                    <thead>
                                                        <tr>
                                                            <th>Rango</th>
                                                            <th>Desde</th>
                                                            <th>Hasta</th>
                                                            <th>Habilitado</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($periodos as $periodo)
                                                            <tr>
                                                                <td>{{ $periodo->descrip_rango }}</td>
                                                                <td>{{ $periodo->desde }}</td>
                                                                <td>{{ $periodo->hasta }}</td>
                                                                @if ($periodo->habilitada == 1)
                                                                    <td>
                                                                        <div
                                                                            class="badge bg-primary text-white rounded-pill-yes-no">
                                                                            SI </div>
                                                                    </td>
                                                                @else
                                                                    <td>
                                                                        <div
                                                                            class="badge bg-danger text-white rounded-pill-yes-no">
                                                                            NO</div>
                                                                    </td>
                                                                @endif
                                                                <td>
                                                                    <button
                                                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2"
                                                                        wire:click="editar_periodo({{ $periodo->id }})">
                                                                        <i
                                                                            class="fa-regular fa-pen-to-square"></i></button>
                                                                    <button type="button"
                                                                        wire:click="$emit('deleteperiodo', {{ $periodo->id }})"
                                                                        title="Borrar encuesta"
                                                                        class="btn btn-datatable btn-icon btn-transparent-dark"><i
                                                                            class="fa-regular fa-trash-can"></i></button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                @if (!empty($periodos))
                                                    {{ $periodos->links() }}
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <h4>Cargue los datos del periodo</h4>
                                        <div class="form-group row">
                                            <label for="descrip_rango"
                                                class="col-sm-2 col-form-label">Descripción</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="descrip_rango"
                                                    name='descrip_rango' placeholder="descripción del periodo"
                                                    wire:model="p_descrip_rango">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group row">
                                                    <label for="desde"
                                                        class="col-sm-4 col-form-label">Desde</label>
                                                    <div class="col-sm-6">
                                                        <input type="date" class="form-control" id="desde"
                                                            name="desde" placeholder="Fecha desde"
                                                            wire:model="p_desde">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group row">
                                                    <label for="hasta"
                                                        class="col-sm-4 col-form-label">Hasta</label>
                                                    <div class="col-sm-6">
                                                        <input type="date" class="form-control" id="hasta"
                                                            name="hasta" placeholder="Fecha hasta"
                                                            wire:model="p_hasta">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="" class="col-sm-2 col-form-label"></label>
                                                <div class="col-sm-4">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            role="switch" id="habilitada" name="habilitada" checked
                                                            wire:model="p_habilitada">
                                                        <label class="form-check-label" for="habilitado">Habilitado
                                                            (No/Si)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <hr class="my-4" />
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-primary"
                                        wire:click="periodo_store">{{ __('Guardar datos') }}</button>
                                </div>
                                <hr class="my-4" />
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-light disabled" type="button" disabled>Previous</button>
                                    <button class="btn btn-primary" type="button"
                                        wire:click="selectTab(3)">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Wizard tab pane item 3 --}}
                    <div class="tab-pane py-5 py-xl-3 fade {{ $currentTab == 3 ? 'show active' : '' }}"
                        id="wizard3" role="tabpanel" aria-labelledby="wizard3-tab">
                        <div class="row justify-content-center">
                            <div class="col-xxl-8 col-xl-10">
                                <h3 class="text-primary">Step 3 - ABM de opciones para una encuesta</h3>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <table class="table table-striped" id="tabla_opciones"
                                                    name="tabla_opciones">
                                                    <thead>
                                                        <tr>
                                                            <th>Opción</th>
                                                            <th>Orden</th>
                                                            <th>Habilitado</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($encuestas_opciones as $opcion)
                                                            <tr>
                                                                <td>{{ $opcion->descripcion }}</td>
                                                                <td>{{ $opcion->orden }}</td>
                                                                @if ($opcion->habilitada == 1)
                                                                    <td>
                                                                        <div
                                                                            class="badge bg-primary text-white rounded-pill-yes-no">
                                                                            SI </div>
                                                                    </td>
                                                                @else
                                                                    <td>
                                                                        <div
                                                                            class="badge bg-danger text-white rounded-pill-yes-no">
                                                                            NO</div>
                                                                    </td>
                                                                @endif
                                                                <td>
                                                                    <button
                                                                        class="btn btn-datatable btn-icon btn-transparent-dark me-2"
                                                                        wire:click="editar_opcion({{ $opcion->id }})">
                                                                        <i
                                                                            class="fa-regular fa-pen-to-square"></i></button>
                                                                    <button type="button"
                                                                        wire:click="$emit('deleteopcion', {{ $opcion->id }})"
                                                                        title="Borrar opcion"
                                                                        class="btn btn-datatable btn-icon btn-transparent-dark"><i
                                                                            class="fa-regular fa-trash-can"></i></button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                @if (!empty($encuestas_opciones))
                                                    {{ $encuestas_opciones->links() }}
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <h4>Cargue los datos de cada opción</h4>
                                        <div class="form-group row">
                                            <label for="user_id_reconocido"
                                                class="col-sm-2 col-form-label">Opción</label>
                                            <div class="col-sm-8">
                                                <select wire:model="o_opciones_id" name="opciones_id"
                                                    class="form-control" id="opciones_id">
                                                    <option value=""> --- Select ---</option>
                                                    @foreach ($opciones as $data)
                                                        <option value="{{ $data->id }}">
                                                            {{ $data->descripcion }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="orden" class="col-sm-2 col-form-label">Orden</label>
                                            <div class="col-sm-2">
                                                <input type="number" min="1" max="{{ count($opciones) }}"
                                                    class="form-control" id="orden" name="orden"
                                                    placeholder="orden" wire:model="o_orden">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="" class="col-sm-2 col-form-label"></label>
                                            <div class="col-sm-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                        id="habilitada" name="habilitada" checked
                                                        wire:model="o_habilitada">
                                                    <label class="form-check-label" for="habilitado">Habilitado
                                                        (No/Si)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <hr class="my-4" />
                                <div class="d-flex justify-content-between">
                                    <button type="buttom" class="btn btn-primary"
                                        wire:click="opcion_store">{{ __('Guardar datos') }}</button>
                                </div>
                                <hr class="my-4" />
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-light disabled" type="button" disabled>Previous</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Wizard tab pane item 4 --}}
                </div>
            </div>
        </div>
    </div>

    @push('scriptscreateenc')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link href="{{ asset('js/util.js') }}" rel="stylesheet">

        <script>
            document.addEventListener("DOMContentLoaded", function(event) {

                window.livewire.on('deleteencuesta', itemId => {
                    Swal.fire({
                        title: 'Confirma borrar el dato?',
                        text: "Encuesta",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Borrar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('borrar_encuesta', itemId);
                            // success response
                        } else {
                            // no se puso nada si cancela
                        }

                    });
                });

                window.livewire.on('deleteperiodo', itemId => {
                    Swal.fire({
                        title: 'Confirma borrar el dato?',
                        text: "Periodo",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Borrar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('borrar_periodo', itemId);
                            // success response
                        } else {
                            // no se puso nada si cancela
                        }

                    });
                });

                window.livewire.on('deleteopcion', itemId => {
                    Swal.fire({
                        title: 'Confirma borrar el dato?',
                        text: "Opción",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Borrar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('borrar_opcion', itemId);
                            // success response
                        } else {
                            // no se puso nada si cancela
                        }

                    });
                });
            });
        </script>
    @endpush
</div>
