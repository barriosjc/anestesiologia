    <div class="row">
        @if(!isset($readonly)) 
            @php($readonly = false)
        @endif
        <div class="col-xl-4">
            <form method="POST" action="{{ route('profile.foto') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ old('user_id', $user->id) }}" />
                <!-- Profile picture card-->
                <div class="card mb-4 mb-xl-0">
                    <div class="card-header">Profile Picture</div>
                    <div class="card-body text-center">
                        <!-- Profile picture image-->
                        <img class="img-account-profile rounded-circle mb-2"
                            src="{{ Storage::disk('usuarios')->url($user->foto) }}" alt="" />
                        <!-- Profile picture help block-->
                        <div class="small font-italic text-muted mb-4">JPG or PNG no mayor a 5 MB</div>
                        <!-- Profile picture upload button-->
                        <input type="file" name="foto" id="foto" class="form-control">
                        <button class="btn btn-primary" type="=submit">Guardar</button>
                    </div>
                </div>
            </form>

            <div class="card">
                <div class="card-header text-white bg-primary">
                    <div class="row">
                        <div class="col">
                            Puntos obtenidos
                        </div>
                        <div class="col">
                            <h3><span class="badge bg-info text-dark">{{ $recibidos }}</span></h3>
                        </div>
                    </div>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach ($reconocimientos as $data)
                        <li class="list-group-item"><i class="fa-regular fa-star-half-stroke"></i> {{ $data->motivo }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-xl-8">
            <!-- Account details card-->
            <div class="card mb-4">
                <div class="card-header">Detalle de usuario</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.save') }}" accept-charset="UTF-8">
                        @csrf
                        <input type="hidden" name="id" value="{{ old('user_id', $user->id) }}" />
                        <div class="mb-3">
                            <label class="small mb-1">Empresa</label>
                            <select name="empresas_id" class="form-control" id="empresas_id" {{$readonly ? 'disabled' : ''}}>
                                <option value=""> --- Select ---</option>
                                @foreach ($empresas as $data)
                                    <option value="{{ $data->id }}"
                                        {{ old('empresas_id', $user->empresas_id) == $data->id ? 'selected' : '' }}>
                                        {{ $data->razon_social }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Form Row-->
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="last_name">Nombre y apellido</label>
                                <input class="form-control" id="last_name" name="last_name" type="text"
                                    placeholder="Ingrese su nombre y apellido"
                                    value="{{ old('last_name', $user->last_name) }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="name">Usuario</label>
                                <input class="form-control" id="name" name="name" type="text"
                                    placeholder="Usuario" value="{{ old('name', $user->name) }}" />
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-sm-6">
                                <label for="jefe_user_id" class="small mb-1">Jefe</label>
                                <select name="jefe_user_id" class="form-control" id="jefe_user_id" {{$readonly ? 'disabled' : ''}}>
                                    <option value=""> --- Select ---</option>
                                    @foreach ($jefes as $data)
                                        <option value="{{ $data->id }}"
                                            {{ old('jefe_user_id', $user->jefe_user_id) == $data->id ? 'selected' : '' }}>
                                            {{ $data->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="cargo">Cargo</label>
                                <input class="form-control" id="cargo" name="cargo" type="text"
                                    placeholder="cargo del empleado" value="{{ old('cargo', $user->cargo) }}" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small mb-1" for="email">Email address</label>
                            <input class="form-control" id="email" name="email" type="email"
                                placeholder="Ingrese su email" value="{{ old('email', $user->email) }}" />
                        </div>
                        <!-- Form Row-->
                        <div class="row gx-3 mb-3">
                            <!-- Form Group (phone number)-->
                            <div class="col-md-5">
                                <label class="small mb-1" for="telefono">Phone number</label>
                                <input class="form-control" id="telefono" name="telefono" type="tel"
                                    placeholder="Ingrese nro de telefono"
                                    value="{{ old('telefono', $user->telefono) }}" />
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1" for="area">Area</label>
                                <input class="form-control" id="area" name="area" type="text"
                                    placeholder="Ingrese nro de area"
                                    value="{{ old('area', $user->area) }}" />
                            </div>
                            <div class="col-md-3">
                                <label for="es_jefe" class="small mb-1">Es jefe</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="es_jefe"
                                        name="es_jefe" {{ $user->es_jefe > 0 ? 'checked' : '' }} {{$readonly ? 'disabled' : ''}}>
                                    <label class="form-check-label" for="es_jefe">
                                        (No/Si)</label>
                                </div>
                            </div>
                        </div>
                        <!-- Form Row-->
                        <div class="row gx-3 mb-3">
                            <!-- Form Group (phone number)-->
                            <div class="col-md-12">
                                <label class="small mb-1" for="observaciones">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" placeholder="Ingrese observaciones"
                                    rows="3">{{ old('observaciones', $user->observaciones) }}</textarea>
                            </div>
                        </div>
                        <!-- Save changes button-->
                        <button class="btn btn-primary" type="submit">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script type="text/javascript">
    $(document).ready(function() {
        var grupalSelect = $('#jefe_user_id');
        var empresasSelect = $('#empresas_id');

        if (empresasSelect.val() > 0) {
            empresasId = empresasSelect.val();
            cargaJefes(empresasId);
        }

        empresasSelect.change(function() {
            var empresasId = $(this).val();
            cargaJefes(empresasId);
        });

        function cargaJefes(empresasId) {

            grupalSelect.empty();
            // var grupalEnBD = null;
            var jefeId = {{ $user->jefe_user_id ? $user->jefe_user_id : 0 }};
            if (empresasId) {
                $.ajax({
                    url: "{{ route('empresas.usuarios') }}",
                    type: 'GET',
                    data: {
                        empresas_id: empresasId
                    },
                    dataType: 'json',
                    success: function(response) {
                        grupalSelect.append("<option value=''> --- Select ---</option>");
                        $.each(response.data, function(key, value) {
                            grupalSelect.append("<option value='" + value.id + "'" +
                                (jefeId !== value.id ? '' : 'selected') +
                                ">" + value.last_name + "</option>");
                        });
                    },
                    error: function(response) {
                        alert(response.messagge);
                    }
                });
            }
        }

        $("a").removeClass("active  ms-0");
        $("#perfil").addClass("active  ms-0");

        if ({{ isset($readonly) ? true : false }}) {
            var formulario = document.getElementById("formreadonly");
            var campos = formulario.getElementsByTagName("input");
            for (var i = 0; i < campos.length; i++) {
                campos[i].readOnly = true;
            }
            var campos = formulario.getElementsByTagName("button");
            for (var i = 0; i < campos.length; i++) {
                campos[i].style.display = "none";
            }

            document.getElementById("foto").disabled = true;
        }

    });
</script>
