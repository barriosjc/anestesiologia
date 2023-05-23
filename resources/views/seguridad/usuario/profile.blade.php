@extends('layouts.main')

@section('titulo', 'Perfil de usuario')
@section('contenido')
    {{-- <div id="layoutSidenav_content"> --}}
    {{-- <main> --}}
    <!-- Main page content-->
    <div class="container-xl px-4 mt-4">
        <!-- Account page navigation-->
        <nav class="nav nav-borders">
            <a class="nav-link active ms-0" href="account-profile.html">Profile</a>
            <a class="nav-link" href="account-billing.html">Billing</a>
            <a class="nav-link" href="account-security.html">Security</a>
            <a class="nav-link" href="account-notifications.html">Notifications</a>
        </nav>
        <hr class="mt-0 mb-4" />
        <div class="row">
            <div class="col-xl-4">
                <form method="POST" action="{{ route('profile') }}" enctype="multipart/form-data">
                    @csrf
                    <!-- Profile picture card-->
                    <div class="card mb-4 mb-xl-0">
                        <div class="card-header">Profile Picture</div>
                        <div class="card-body text-center">
                            <!-- Profile picture image-->
                            <img class="img-account-profile rounded-circle mb-2"
                                src="assets/img/illustrations/profiles/profile-1.png" alt="" />
                            <!-- Profile picture help block-->
                            <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 5 MB</div>
                            <!-- Profile picture upload button-->
                            <button class="btn btn-primary" type="button">Upload new image</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-xl-8">
                <!-- Account details card-->
                <div class="card mb-4">
                    <div class="card-header">Detalle de usuario</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile') }}" accept-charset="UTF-8">
                            @csrf
                            <input type="hidden" name="id" value="{{old('user_id', $user->id)}}" />
                            <div class="mb-3">
                                <label class="small mb-1">Empresa</label>
                                <select name="empresas_id" class="form-control" id="empresas_id">
                                    <option value=""> --- Select ---</option>
                                    @foreach ($empresas as $data)
                                        <option value="{{ $data->id }}" {{old('empresas_id', $user->empresas_id)==$data->id ? 'selected' : ''}}>
                                            {{ $data->razon_social }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Form Row-->
                            <div class="row gx-3 mb-3">
                                <!-- Form Group (first name)-->
                                <div class="col-md-6">
                                    <label class="small mb-1" for="last_name">Nombre y apellido</label>
                                    <input class="form-control" id="last_name" name="last_name" type="text"
                                        placeholder="Ingrese su nombre y apellido" value="{{old('last_name', $user->last_name)}}" />
                                </div>
                                <!-- Form Group (last name)-->
                                <div class="col-md-6">
                                    <label class="small mb-1" for="name">Usuario</label>
                                    <input class="form-control" id="name" name="name" type="text"
                                        placeholder="Usuario" value="{{old('name', $user->name)}}" />
                                </div>
                            </div>
                            <!-- Form Row        -->
                            <div class="row gx-3 mb-3">
                                <!-- Form Group (organization name)-->
                                <div class="col-sm-6">
                                    <label for="grupal_id"  class="small mb-1">Area</label>
                                    <select name="grupal_id" class="form-control" id="grupal_id">
                                        <option value=""> --- Select ---</option>
                                    </select>
                                </div>
                                <!-- Form Group (location)-->
                                <div class="col-md-6">
                                    <label class="small mb-1" for="cargo">Cargo</label>
                                    <input class="form-control" id="cargo" name="cargo" type="text"
                                        placeholder="cargo del empleado" value="{{old('cargo', $user->cargo)}}" />
                                </div>
                            </div>
                            <!-- Form Group (email address)-->
                            <div class="mb-3">
                                <label class="small mb-1" for="email">Email address</label>
                                <input class="form-control" id="email" name="email" type="email"
                                    placeholder="Ingrese su email" value="{{old('email', $user->email)}}" />
                            </div>
                            <!-- Form Row-->
                            <div class="row gx-3 mb-3">
                                <!-- Form Group (phone number)-->
                                <div class="col-md-6">
                                    <label class="small mb-1" for="telefono">Phone number</label>
                                    <input class="form-control" id="telefono" name="telefono" type="tel"
                                        placeholder="Ingrese nro de telefono" value="{{old('telefono', $user->telefono)}}" />
                                </div>
                            </div>
                            <!-- Form Row-->
                            <div class="row gx-3 mb-3">
                                <!-- Form Group (phone number)-->
                                <div class="col-md-12">
                                    <label class="small mb-1" for="observaciones">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" 
                                        placeholder="Ingrese observaciones" rows="3" value="{{old('observaciones', $user->observaciones)}}"></textarea>
                                </div>
                            </div>
                            <!-- Save changes button-->
                            <button class="btn btn-primary" type="submit">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- </main> --}}
    {{-- </div> --}}
    <script type="text/javascript">
        $(document).ready(function() {

            
            var grupalSelect = $('#grupal_id');
            var empresasSelect = $('#empresas_id');
            empresasSelect.change(function(){
                var empresasId = $(this).val();
                grupalSelect.empty();
                var grupalEnBD = null;
                @isset($publisher)
                    grupalEnBD = '{{ $user->grupal_id }}';
                @endisset

                if (empresasId) {
                    $.ajax({
                        url: "{{ route('empresas.grupos') }}",
                        type: 'GET',
                        data: { empresas_id: empresasId },
                        dataType: 'json',
                        success: function (response) {
                            grupalSelect.append("<option value=''> --- Select ---</option>");
                            $.each(response.data, function (key, value) {
                                grupalSelect.append("<option value='" + value.id + "'>" + value.descripcion + "</option>");
                            });
                            grupalSelect.val( grupalEnBD ? grupalEnBD : $("#grupal_id option:first").val() )
                                .find("option[value=" + grupalEnBD +"]").attr('selected', true)
                                .trigger('change');
                        },
                        error : function(response){
                            alert(response.messagge);
                        }
                    });
                }
            });
        });
</script>
@endsection
