<div>
    <div class="card shadow-lg border-0 rounded-lg mt-5">
        <div class="card-header justify-content-center">
            <div class="row">
                <div class="col-lg-6 text-start">
                    <h3 class="fw-light my-4">Login</h3>
                    <h5 class="fw-light my-4">¡Te damos la bienvenida!</h5>
                </div>
                <div class="col-lg-6 d-flex align-items-center justify-content-center">
                    <img class="dropdown-user-img" style="height:65px;width:130px"
                        src="{{ asset('img/logomedinexustransparente.png') }}" />
                </div>
            </div>
        </div>

        <div class="card-body">
            <form wire:submit="ingresar">
                <div class="mb-3">
                    <label class="small mb-1" for="email">Email</label>
                    <input id="email" type="email"
                        class="form-control @error('email') is-invalid @enderror" wire:model="email"
                        placeholder="Enter email address" required autocomplete="email" autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="small mb-1" for="password">Password</label>
                    <input id="password" type="password"
                        class="form-control @error('password') is-invalid @enderror" wire:model="password"
                        placeholder="Enter password" required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                    <a class="small" href="{{ route('login.restablecer') }}">Olvido su password?</a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('Login') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
