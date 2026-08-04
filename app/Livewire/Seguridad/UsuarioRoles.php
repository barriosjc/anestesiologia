<?php

namespace App\Livewire\Seguridad;

use App\Models\Role;
use App\Models\user;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class UsuarioRoles extends Component
{
    public int $usuid;

    public function mount(int $id): void
    {
        $this->usuid = $id;
    }

    public function asignar(int $rolid): void
    {
        $user = user::findOrFail($this->usuid);
        $rol = Role::findOrFail($rolid);

        try {
            $user->assignRole($rol);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo asignar el rol.');

            return;
        }

        session()->flash('success', 'Rol asignado correctamente.');
    }

    public function desasignar(int $rolid): void
    {
        $user = user::findOrFail($this->usuid);
        $rol = Role::findOrFail($rolid);

        try {
            $user->removeRole($rol);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo quitar el rol.');

            return;
        }

        session()->flash('success', 'Rol quitado correctamente.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $user = user::findOrFail($this->usuid);
        $titulo = 'asignados al usuario -> ' . strtoupper($user->name);

        $roles = $user->Roles()->get();
        $roless = Role::whereNotIn('id', $user->Roles()->pluck('id'))->get();

        return view('livewire.seguridad.usuario-roles', compact('user', 'titulo', 'roles', 'roless'));
    }
}
