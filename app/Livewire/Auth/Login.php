<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.login')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public function ingresar(): void
    {
        $this->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login:' . request()->ip() . '|' . $this->email;

        $success = RateLimiter::attempt($key, 5, function () {
            return Auth::attempt(['email' => $this->email, 'password' => $this->password]);
        }, 60);

        if ($success) {
            $this->redirect(route('main'));

            return;
        }

        if (RateLimiter::tooManyAttempts($key)) {
            $this->addError('email', 'Demasiados intentos de login. Intente de nuevo en ' . RateLimiter::availableIn($key) . ' segundos.');
        } else {
            $this->addError('email', 'Estas credenciales no coinciden con nuestros registros.');
        }

        $this->password = '';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.auth.login');
    }
}
