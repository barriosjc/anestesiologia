@php
    use Carbon\Carbon;
@endphp
<div class="box box-info padding-1">
    <input type="hidden" name="parte_id" id="parte_id" value="{{old('parte_id', $parte_id)}}">
    <div class="box-body">
        <div class="row gx-3 mb-3">
            <div class="col-md-8">
                <label class="small mb-1">Centro</label>
                <select name="centro_id" class="form-select" id="centro_id" required>
                    <option value=""> -- Seleccione --</option>
                    @foreach ($centros as $data)
                        <option value="{{ $data->id }}" {{old('centro_id', $parte->centro_id) == $data->id ? 'selected' : ''}}>    
                            {{ $data->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="small mb-1" for="fec_prestacion">Fecha cirugía</label>
                <input class="form-control" id="fec_prestacion" name="fec_prestacion" type="date"
                    placeholder="Ingrese Fecha de nacimiento"
                    value="{{ old('fec_prestacion', Carbon::parse($parte->fec_prestacion)->format('Y-m-d')) }}" />
            </div>
        </div>
        <div class="row gx-3 mb-3">
            <div class="col-md-3">
                <label class="small mb-1" for="dni">DNI</label>
                <div class="input-group">
                    <input class="form-control" id="dni" name="dni" type="text" placeholder="dni"
                        value="{{ old('dni', $paciente->dni) }}" required />
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <label class="small mb-1" for="nombre">Nombre y apellido/a</label>
                <input class="form-control" id="nombre" name="nombre" 
                    placeholder="Ingrese su nombre y apellido" value="{{ old('nombre', $paciente->nombre) }}" />
            </div>
            <div class="col-md-4">
                <label class="small mb-1" for="fec_nacimiento">Fec. Nac.</label>
                <input class="form-control" id="fec_nacimiento" name="fec_nacimiento" type="date"
                    placeholder="Ingrese Fecha de nacimiento"
                    value="{{ old('fec_nacimiento', $paciente->fec_nacimiento) }}" />
            </div>
        </div>

        <!-- Form Row-->
        <div class="row gx-3 mb-3">
            <div class="col-md-6 custom-select2">
                <label class="small mb-1">Coberturas</label>
                <select name="cobertura_id" class="form-control select2" id="cobertura_id" required>
                    <option value="">-- Seleccione --</option>
                    @foreach ($coberturas as $data)
                        <option value="{{ $data->id }}" {{old('cobertura_id', $parte->cobertura_id) == $data->id ? 'selected' : ''}}>
                            {{ $data->sigla }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="small mb-1">Profesional</label>
                <select name="profesional_id" class="form-select" id="profesional_id" required>
                    <option value=""> -- Seleccione --</option>
                    @foreach ($profesionales as $data)
                        <option value="{{ $data->id }}" {{old('profesional_id', $parte->profesional_id) == $data->id ? 'selected' : ''}}> 
                            {{ $data->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row gx-3 mb-3">
            <div class="col-12">
                <label class="small mb-1">Observaciones</label>
                <textarea class="form-control" name="observaciones" rows='4'>{{ old('observaciones', $parte->observaciones) }}</textarea>
            </div>
        </div>

        <div class="box-footer mt20">
            <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
            @php($parte_id = $parte_id ?? session('parte_id'))
            @if(!empty($parte_id))
                <a href="{{route('partes_det.create', $parte_id)}}" class="btn btn-success">{{ __('Cargar detalle') }}</a>
            @endif
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $(document).ready(function() {
                $('.select2').select2();
            });

            $('#search-button').on('click', function() {
                var dni = $('#dni').val();
                $('#alert-container').hide().empty();
                $('#nombre').val("");
                $('#fec_nacimiento').val("");
                // $('#paciente_id').val("");

                if (dni) {
                    $.ajax({
                        url: '{{ route('pacientes.buscar') }}',
                        method: 'GET',
                        data: {
                            dni: dni
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#nombre').val(response.data.nombre);
                                $('#fec_nacimiento').val(response.data.fec_nacimiento);
                                // $('#paciente_id').val(response.id);
                            } else {
                                showAlert('No se ha encontrado al paciente con este DNI.',
                                    'danger');
                            }
                        },
                        error: function() {
                            showAlert('Error al buscar el paciente.', 'danger');
                        }
                    });
                } else {
                    showAlert('Por favor ingredar un DNI', 'danger');
                }
            });

            function showAlert(message, type) {
                let alertBox = `<div class="alert alert-${type} py-2 alert-dismissible fade show" role="alert">
                                ${message}
                            </div>`;
                //carga el alert y mueve el puntero al inicio de pantalla para ver msg
                $('#alert-container').html(alertBox).show();
                const ytop = $('#alert-container').offset().top;
                window.scrollTo({
                    top: ytop - 300, 
                    behavior: 'smooth'
                });
            }
        });
    </script>
