<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<input type="hidden" name="id" value="{{ old('user_id', $user->id) }}" />
<div class="row">
    <div class="col-md-6">
        <label class="small mb-1" for="name">Usuario</label>
        <input class="form-control" id="name" name="name" type="text" placeholder="Usuario"
            value="{{ old('name', $user->name) }}" />
    </div>
    <div class="col-md-6">
        <label class="small mb-1" for="email">Email </label>
        <input class="form-control" id="email" name="email" type="email" placeholder="Ingrese su email"
            value="{{ old('email', $user->email) }}" />
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <label class="small mb-1">Perfil/es</label>
        <select name="perfil_id[]" class="form-control" id="perfil_id" multiple>
        </select>
    </div>
    <div class="col-md-6">
        <label class="small mb-1">Centro</label>
        <select name="centro_id" class="form-select" id="centro_id">
            @foreach ($centros as $data)
                <option value="{{ $data->id }}"
                    {{ $data->id == old('centro_id', $user->centro_id) ? 'selected' : '' }}>
                    {{ $data->nombre }}</option>
            @endforeach
        </select>

        <div class="form-check form-switch pt-3">
            <input class="form-check-input" type="checkbox" role="switch" name="blanquear" id="blanquear" >
            <label class="form-check-label" for="blanquear">Blanquear clave</label>
          </div>
    </div>
</div>

<div class="col-12">
    <!-- Save changes button-->
    <button class="btn btn-primary" type="submit">Guardar</button>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var rolesSelect = $('#perfil_id');
        rolesSelect.empty();

        const lroles = '{{ $perfiles_user }}';
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
