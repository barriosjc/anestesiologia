<div>
    <div class="card mt-5 shadow-lg border-0 rounded-lg">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>{{ __('Restablecer Password') }}</span>
            <a href="{{ route('login') }}">
                <button class="btn btn-warning btn-sm px-3 py-2">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver al login
                </button>
            </a>
        </div>
        <div class="card-body">
            @if ($status)
                <div class="alert alert-success" role="alert">
                    {{ $status }}
                </div>
            @endif

            <form wire:submit="enviar">
                <div class="row mb-3">
                    <label for="email" class="col-md-3 col-form-label text-md-end">{{ __('Email Address') }}</label>

                    <div class="col-md-9">
                        <input id="email" type="email"
                            class="form-control @error('email') is-invalid @enderror" wire:model="email"
                            required autocomplete="email" autofocus>

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Enviar correo con nueva clave') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
