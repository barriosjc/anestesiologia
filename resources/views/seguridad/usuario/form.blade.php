<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<input type="hidden" name="id" value="{{ old('user_id', $user->id) }}" />
<div class="mb-3">
    <label class="small mb-1">Empresa</label>
    <select name="empresas_id" class="form-control" id="empresas_id">
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
            placeholder="Ingrese su nombre y apellido" value="{{ old('last_name', $user->last_name) }}" />
    </div>
    <div class="col-md-6">
        <label class="small mb-1" for="name">Usuario</label>
        <input class="form-control" id="name" name="name" type="text" placeholder="Usuario"
            value="{{ old('name', $user->name) }}" />
    </div>
</div>
<div class="row gx-3 mb-3">
    <div class="col-sm-6">
        <label for="jefe_user_id" class="small mb-1">Jefe</label>
        <select name="jefe_user_id" class="form-control" id="jefe_user_id">
            {{-- <option value=""> --- Select ---</option>
                     @foreach ($jefes as $data)
                        <option value="{{ $data->id }}"
                            {{ old('jefe_user_id', $user->jefe_user_id) == $data->id ? 'selected' : '' }}>
                            {{ $data->last_name }}</option>
                    @endforeach --}}
        </select>
    </div>
    <div class="col-md-6">
        <label class="small mb-1" for="cargo">Cargo</label>
        <input class="form-control" id="cargo" name="cargo" type="text" placeholder="cargo del empleado"
            value="{{ old('cargo', $user->cargo) }}" />
    </div>
</div>
<div class="mb-3">
    <label class="small mb-1" for="email">Email address</label>
    <input class="form-control" id="email" name="email" type="email" placeholder="Ingrese su email"
        value="{{ old('email', $user->email) }}" />
</div>
<!-- Form Row-->
<div class="row gx-3 mb-3">
    <!-- Form Group (phone number)-->
    <div class="col-md-6">
        <label class="small mb-1" for="telefono">Phone number</label>
        <input class="form-control" id="telefono" name="telefono" type="tel" placeholder="Ingrese nro de telefono"
            value="{{ old('telefono', $user->telefono) }}" />
    </div>
    <div class="col-md-6">
        <label for="es_jefe" class="small mb-1">Es jefe</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="es_jefe" name="es_jefe"
                {{ $user->es_jefe > 0 ? 'checked' : '' }}>
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

<div class="mb-3">
    <label class="small mb-1">Perfil/es</label>
    <select name="perfil_id[]" class="form-control" id="perfil_id" multiple>
        {{-- @foreach ($perfiles as $data)
            <option value="{{ $data->id }}" {{ in_array($data->id, $perfiles_user) ? 'selected' : '' }}>
                {{ $data->name }}</option>
        @endforeach --}}
    </select>
</div>
<div class="col-12">
    <!-- Save changes button-->
    <button class="btn btn-primary" type="submit">Guardar</button>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var grupalSelect = $('#jefe_user_id');
        var empresasSelect = $('#empresas_id');
        var rolesSelect = $('#perfil_id');

        if (empresasSelect.val() > 0) {
            empresasId = empresasSelect.val();
            cargaJefes(empresasId);
        }

        empresasSelect.change(function() {
            var empresasId = $(this).val();
            cargaJefes(empresasId);
            cargaRoles(empresasId);
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

        function cargaRoles(empresasId) {

            rolesSelect.empty();
            // var grupalEnBD = null;
            var roleId = 0 ; //{{ $perfiles_user ? $perfiles_user : null }};
            if (empresasId) {
                $.ajax({
                    url: "{{ route('empresas.roles') }}",
                    type: 'GET',
                    data: {
                        empresas_id: empresasId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $.each(response.data, function(key, value) {
                            rolesSelect.append("<option value='" + value.id + "'" +
                                (roleId !== value.id ? '' : 'selected') +
                                ">" + value.name + "</option>");
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
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
<script>
    $(document).ready(function() {
        $('#perfil_id').select2({
            theme: 'bootstrap-5'
        });

        // for (let index = 0; index < $.length; index++) {
        //   const element = array[index];

        // }

    });
</script>
