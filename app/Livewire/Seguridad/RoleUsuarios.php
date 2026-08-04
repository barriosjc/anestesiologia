<?php

namespace App\Livewire\Seguridad;

use App\Models\Role;
use App\Models\user;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class RoleUsuarios extends Component
{
    public int $rolid;

    public function mount(int $id): void
    {
        $this->rolid = $id;
    }

    public function asignar(int $usuid): void
    {
        $user = user::findOrFail($usuid);
        $rol = Role::findOrFail($this->rolid);

        try {
            $user->assignRole($rol);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo asignar el usuario.');

            return;
        }

        session()->flash('success', 'Usuario asignado correctamente.');
    }

    public function desasignar(int $usuid): void
    {
        $user = user::findOrFail($usuid);
        $rol = Role::findOrFail($this->rolid);

        try {
            $user->removeRole($rol);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo quitar el usuario.');

            return;
        }

        session()->flash('success', 'Usuario quitado correctamente.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $rol = Role::findOrFail($this->rolid);
        $titulo = 'asignados al rol -> ' . strtoupper($rol->name);

        $user = $rol->users()->get();
        $users = user::whereNotIn('id', $rol->users()->pluck('id'))->get();

        return view('livewire.seguridad.rol-usuarios', compact('rol', 'titulo', 'user', 'users'));
    }
}
