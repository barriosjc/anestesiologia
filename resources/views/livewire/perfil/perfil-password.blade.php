<div>
    <div class="container-xl px-4 mt-4">
        <!-- Account page navigation-->
        <nav class="nav nav-borders">
            <a class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}" id="perfil"
                href="{{ route('profile', ['id' => Auth()->user()->id]) }}">Perfil usuario</a>
            <a class="nav-link {{ request()->routeIs('profile.password') ? 'active' : '' }}" id="password"
                href="{{ route('profile.password') }}">Cambio de password</a>
        </nav>
        <hr class="mt-0 mb-4" />

        @include('utiles.alerts')

        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9">
                <!-- Social registration form-->
                <div class="card my-5">
                    <div class="card-body p-5 text-center">
                        <div class="h3 fw-light mb-3">Cambio de clave</div>
                        <div class="small text-muted mb-2">{{ Auth()->user()->name }}</div>
                    </div>

                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="text-gray-600 small" for="password_actual">Password actual</label>
                            <input class="form-control form-control-solid" type="password" placeholder=""
                                wire:model="password_actual" id="password_actual" />
                            @error('password_actual') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row gx-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-gray-600 small" for="password_nueva">Nueva Password</label>
                                    <input class="form-control form-control-solid" type="password" placeholder=""
                                        wire:model="password_nueva" id="password_nueva" />
                                    @error('password_nueva') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-gray-600 small" for="confirmacion_password">Confirmación de
                                        Password</label>
                                    <input class="form-control form-control-solid" type="password" placeholder=""
                                        wire:model="confirmacion_password" id="confirmacion_password" />
                                    @error('confirmacion_password') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <p class="text-danger small">La nueva clave debe tener 8 caracteres mínimo, contener letras
                            mayusculas, minúsculas y numeros.</p>

                        <hr class="my-0" />
                        <div class="row gx-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <button wire:click="guardar" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
