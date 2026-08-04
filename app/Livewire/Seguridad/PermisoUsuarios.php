<?php

namespace App\Livewire\Seguridad;

use App\Models\user;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Layout('layouts.main')]
class PermisoUsuarios extends Component
{
    public int $perid;

    public function mount(int $id): void
    {
        $this->perid = $id;
    }

    public function asignar(int $usuid): void
    {
        $per = Permission::findOrFail($this->perid);
        $user = user::findOrFail($usuid);

        try {
            $user->givePermissionTo($per);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo asignar el usuario.');

            return;
        }

        session()->flash('success', 'Usuario asignado correctamente.');
    }

    public function desasignar(int $usuid): void
    {
        $per = Permission::findOrFail($this->perid);
        $user = user::findOrFail($usuid);

        try {
            $user->revokePermissionTo($per);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo quitar el usuario.');

            return;
        }

        session()->flash('success', 'Usuario quitado correctamente.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $per = Permission::findOrFail($this->perid);
        $titulo = 'asignados al permiso -> ' . strtoupper($per->name);

        $user = $per->users()->get();
        $users = user::whereNotIn('id', $per->users()->pluck('id'))->get();

        return view('livewire.seguridad.permiso-usuarios', compact('per', 'titulo', 'user', 'users'));
    }
}
