    <div class="row">
        @if (!isset($readonly))
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
                        <!-- Profile picture upload button-->
                        <input type="file" name="foto" id="foto" class="form-control"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="JPG or PNG no mayor a 5 MB">
                        <button class="btn btn-primary" type="=submit">Guardar</button>
                    </div>
                </div>
            </form>
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
                            <label class="small mb-1">Centro</label>
                            <label class="form-control">{{ $user->centro->nombre ?? "Todos" }}</label>
                        </div>
                        <!-- Form Row-->
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="name">Nombre y apellido</label>
                                <input class="form-control" id="name" name="name" type="text"
                                    placeholder="Ingrese su nombre y apellido"
                                    value="{{ old('name', $user->name) }}" />
                            </div>
                            <div class="col-md-5">
                                <label class="small mb-1" for="telefono">Telefono</label>
                                <input class="form-control" id="telefono" name="telefono" type="tel"
                                    placeholder="Ingrese nro de telefono"
                                    value="{{ old('telefono', $user->telefono) }}" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="small mb-1" for="email">Email </label>
                            <input class="form-control" id="email" name="email" type="email"
                                placeholder="Ingrese su email" value="{{ old('email', $user->email) }}" />
                        </div>
                        <!-- Form Row-->

                        <!-- Save changes button-->
                        <button class="btn btn-primary" type="submit">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {

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
