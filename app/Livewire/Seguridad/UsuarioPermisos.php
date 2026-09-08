<?php

namespace App\Livewire\Seguridad;

use App\Models\user;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Layout('layouts.main')]
class UsuarioPermisos extends Component
{
    public int $usuid;

    public function mount(int $id): void
    {
        $this->usuid = $id;
    }

    public function asignar(int $perid): void
    {
        $user = user::findOrFail($this->usuid);
        $per = Permission::findOrFail($perid);

        try {
            $user->givePermissionTo($per);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo asignar el permiso.');

            return;
        }

        session()->flash('success', 'Permiso asignado correctamente.');
    }

    public function desasignar(int $perid): void
    {
        $user = user::findOrFail($this->usuid);
        $per = Permission::findOrFail($perid);

        try {
            $user->revokePermissionTo($per);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo quitar el permiso.');

            return;
        }

        session()->flash('success', 'Permiso quitado correctamente.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $user = user::findOrFail($this->usuid);
        $titulo = 'asignados al usuario -> ' . strtoupper($user->name);

        $permisos = $user->permissions()->get();
        $permisoss = Permission::whereNotIn('id', $user->permissions()->pluck('id'))->get();

        return view('livewire.seguridad.usuario-permisos', compact('user', 'titulo', 'permisos', 'permisoss'));
    }
}
