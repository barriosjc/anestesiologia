<?php

namespace App\Livewire\Perfil;

use App\Models\user;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class PerfilPassword extends Component
{
    public ?string $password_actual = null;
    public ?string $password_nueva = null;
    public ?string $confirmacion_password = null;

    public function guardar(): void
    {
        $this->validate([
            'password_actual' => [
                'required', 'string', 'max:20',
                function ($attribute, $value, $fail) {
                    $usuario = user::find(Auth()->user()->id);
                    if (!($usuario && Hash::check($value, $usuario->password))) {
                        $fail('La clave actual que ingreso es incorrecta.');
                    }
                }
            ],
            'password_nueva' => [
                'required', 'string', 'min:8', 'max:20',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'different:password_actual'
            ],
            'confirmacion_password' => [
                'required', 'string', 'min:8', 'max:20', 'same:password_nueva'
            ],
        ], [
            'password_actual.required' => 'Debe ingresar la clave actual.',
            'password_nueva.required'  => 'Debe ingresar la nueva clave.',
            'password_nueva.regex'     => 'La nueva clave debe contener letras mayúsculas, minúsculas y números.',
            'password_nueva.different' => 'La nueva clave debe ser distinta a la actual.',
            'password_nueva.min'       => 'La nueva clave debe tener al menos 8 caracteres.',
            'confirmacion_password.required' => 'Debe confirmar la nueva clave.',
            'confirmacion_password.same'     => 'La confirmación debe coincidir con la nueva clave.',
        ]);

        try {
            $user = user::findOrFail(Auth()->user()->id);
            $user->password = Hash::make($this->password_nueva);
            $user->cambio_password = 0;
            $user->save();
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo actualizar la password.');

            return;
        }

        $this->reset(['password_actual', 'password_nueva', 'confirmacion_password']);
        session()->flash('success', 'Se actualizó la nueva password correctamente.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.perfil.perfil-password');
    }
}
