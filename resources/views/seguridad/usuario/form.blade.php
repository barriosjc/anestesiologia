<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<input type="hidden" name="id" value="{{ old('user_id', $user->id) }}" />
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
<div class="mb-3">
    <label class="small mb-1" for="email">Email </label>
    <input class="form-control" id="email" name="email" type="email" placeholder="Ingrese su email"
        value="{{ old('email', $user->email) }}" />
</div>
<!-- Form Row-->
<div class="row gx-3 mb-3">
    <!-- Form Group (phone number)-->

    <div class="col-md-3">
        <label for="activo" class="small mb-1">Activo</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo"
                {{ $user->activo > 0 ? 'checked' : '' }}>
            <label class="form-check-label" for="activo">
                (No/Si)</label>
        </div>
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
{{-- <div class="mb-3">
    <label class="small mb-1">profesionales</label>
    <select name="profesional_id[]" class="form-control" id="profesional_id" multiple>
        @foreach ($perfiles as $data)
            <option value="{{ $data->id }}" {{ in_array($data->id, $perfiles_user) ? 'selected' : '' }}>
                {{ $data->name }}</option>
        @endforeach 
    </select>
</div> --}}

<div class="col-12">
    <!-- Save changes button-->
    <button class="btn btn-primary" type="submit">Guardar</button>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var rolesSelect = $('#perfil_id');
        rolesSelect.empty();

        const lroles = '{{$perfiles_user}}';
        $.ajax({
            url: "{{ route('roles.json') }}",
            type: 'GET',
            data: null,
            dataType: 'json',
            success: function(response) {
                $.each(response.data, function(key, value) {
                    rolesSelect.append("<option value='" + value.id + "'" +
                        (lroles.includes(value.id) ? 'selected' : '') +
                        ">" + value.name + "</option>");
                });
            },
            error: function(response) {
                alert(response.messagge);
            }
        });
        
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
