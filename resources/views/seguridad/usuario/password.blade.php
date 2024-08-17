@extends('seguridad.usuario.profile')

@section('profield')
    <div class="container-xl px-4">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9">
                <!-- Social registration form-->
                <div class="card my-5">
                    <div class="card-body p-5 text-center">
                        <div class="h3 fw-light mb-3">Cambio de clave</div>
                        <div class="small text-muted mb-2">{{ Auth()->user()->name }}</div>
                    </div>

                    <div class="card-body p-3">
                        <form method="POST" action="{{ route('profile.password.save') }}" role="form">
                            @csrf
                            <!-- Form Row-->
                            <!-- Form Group (email address)-->
                            <div class="mb-3">
                                <label class="text-gray-600 small" for="password_actual">Password actual</label>
                                <input class="form-control form-control-solid" type="password" placeholder=""
                                    name="password_actual" aria-label="Email Address" aria-describedby="password_actual" />
                            </div>
                            <!-- Form Row-->
                            <div class="row gx-3">
                                <div class="col-md-6">
                                    <!-- Form Group (choose password)-->
                                    <div class="mb-3">
                                        <label class="text-gray-600 small" for="password_nueva">Nueva Password</label>
                                        <input class="form-control form-control-solid" type="password" placeholder=""
                                            name="password_nueva" aria-label="Password" aria-describedby="password_nueva" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Form Group (confirm password)-->
                                    <div class="mb-3">
                                        <label class="text-gray-600 small" for="confirmacion_password">Confirmación de
                                            Password</label>
                                        <input class="form-control form-control-solid" type="password" placeholder=""
                                            name="confirmacion_password" aria-label="Confirm Password"
                                            aria-describedby="confirmacion_password" />
                                    </div>
                                </div>
                            </div>
                            <p class="text-danger small">La nueva clave debe tener 8 caracteres mínimo, contener letras
                                mayusculas, minúsculas y numeros.</p>

                            <hr class="my-0" />
                            <!-- Form Group (form submission)-->
                            <div class="row gx-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <input type="submit" class="btn btn-primary  " href="auth-login-social.html"
                                            value='Guardar'>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $("a").removeClass("active  ms-0");
            $("#password").addClass("active  ms-0");
        });
    </script>
@endsection
