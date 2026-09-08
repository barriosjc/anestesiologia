<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
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

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->redirect(route('main'));

            return;
        }

        $this->addError('email', 'Estas credenciales no coinciden con nuestros registros.');
        $this->password = '';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.auth.login');
    }
}
