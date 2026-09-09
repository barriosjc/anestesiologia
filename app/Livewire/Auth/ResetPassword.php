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
<<<<<<< HEAD
            Mail::send([], [], function ($message) use ($user, $correo) {
                $message->to($user->email, $user->name)
                    ->subject('Cambio de clave para ingreso al portal')
                    ->setBody($correo->render(), 'text/html');
            });
=======
            Mail::to($user)->send($correo);
>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856
        }

        $this->status = 'Se le ha enviado un email a ' . $validated['email'] . ' con su nueva clave.';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.auth.reset-password');
    }
}
