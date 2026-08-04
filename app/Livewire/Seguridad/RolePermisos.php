<?php

namespace App\Livewire\Seguridad;

use App\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Layout('layouts.main')]
class RolePermisos extends Component
{
    public int $rolid;

    public function mount(int $id): void
    {
        $this->rolid = $id;
    }

    public function asignar(int $perid): void
    {
        $rol = Role::findOrFail($this->rolid);
        $per = Permission::findOrFail($perid);

        try {
            $rol->givePermissionTo($per);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo asignar el permiso.');

            return;
        }

        session()->flash('success', 'Permiso asignado correctamente.');
    }

    public function desasignar(int $perid): void
    {
        $rol = Role::findOrFail($this->rolid);
        $per = Permission::findOrFail($perid);

        try {
            $rol->revokePermissionTo($per);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo quitar el permiso.');

            return;
        }

        session()->flash('success', 'Permiso quitado correctamente.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $rol = Role::findOrFail($this->rolid);
        $titulo = 'asignados al rol -> ' . strtoupper($rol->name);

        $permisos = $rol->permissions()->get();
        $permisoss = Permission::whereNotIn('id', $rol->permissions()->pluck('id'))
            ->where('guard_name', 'web')
            ->get();

        return view('livewire.seguridad.rol-permisos', compact('rol', 'titulo', 'permisos', 'permisoss'));
    }
}
