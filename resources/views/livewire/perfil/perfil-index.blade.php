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

        <div class="row">
            <div class="col-xl-4">
                <!-- Profile picture card-->
                <div class="card mb-4 mb-xl-0">
                    <div class="card-header">Profile Picture</div>
                    <div class="card-body text-center">
                        <img class="img-account-profile rounded-circle mb-2"
                            src="{{ Storage::disk('usuarios')->url($user->foto) }}" alt="" />
                        <input type="file" wire:model="foto" id="foto" class="form-control"
                            data-bs-toggle="tooltip" data-bs-placement="right"
                            title="JPG or PNG no mayor a 5 MB">
                        @error('foto') <span class="text-danger small">{{ $message }}</span> @enderror
                        <div class="mt-2">
                            <button class="btn btn-primary" wire:click="guardarFoto">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <!-- Account details card-->
                <div class="card mb-4">
                    <div class="card-header">Detalle de usuario</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="small mb-1">Centro</label>
                            <label class="form-control">{{ $user->centro->nombre ?? 'Todos' }}</label>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="name">Nombre y apellido</label>
                                <input class="form-control" id="name" wire:model="name" type="text"
                                    placeholder="Ingrese su nombre y apellido" />
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-5">
                                <label class="small mb-1" for="telefono">Telefono</label>
                                <input class="form-control" id="telefono" wire:model="telefono" type="tel"
                                    placeholder="Ingrese nro de telefono" />
                                @error('telefono') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small mb-1" for="email">Email</label>
                            <input class="form-control" id="email" wire:model="email" type="email"
                                placeholder="Ingrese su email" />
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <button class="btn btn-primary" wire:click="guardar">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
