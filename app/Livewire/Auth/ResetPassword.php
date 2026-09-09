<?php

namespace App\Livewire\Auth;

use App\Mail\ResetpasswordMaillable;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.login')]
class ResetPassword extends Component
{
    public string $email = '';

    public ?string $status = null;

    public function enviar(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'string', 'max:200'],
        ]);

        $user = User::where('email', $this->email)->first();

        if (! empty($user)) {
            $clave = bin2hex(random_bytes(5));
            $user->password = Hash::make($clave);
            $user->cambio_password = 1;
            $user->save();

            $correo = new ResetpasswordMaillable($user, $clave);
            Mail::to($user)->send($correo);
        }

        $this->status = 'Se le ha enviado un email a ' . $validated['email'] . ' con su nueva clave.';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.auth.reset-password');
    }
}
