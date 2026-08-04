<?php

namespace App\Livewire\Seguridad;

use App\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Layout('layouts.main')]
class PermisoRoles extends Component
{
    public int $perid;

    public function mount(int $id): void
    {
        $this->perid = $id;
    }

    public function asignar(int $rolid): void
    {
        $per = Permission::findOrFail($this->perid);
        $rol = Role::findOrFail($rolid);

        try {
            $per->assignRole($rol);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo asignar el rol.');

            return;
        }

        session()->flash('success', 'Rol asignado correctamente.');
    }

    public function desasignar(int $rolid): void
    {
        $per = Permission::findOrFail($this->perid);
        $rol = Role::findOrFail($rolid);

        try {
            $per->removeRole($rol);
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo quitar el rol.');

            return;
        }

        session()->flash('success', 'Rol quitado correctamente.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $per = Permission::findOrFail($this->perid);
        $titulo = 'asignados al permiso -> ' . strtoupper($per->name);

        $roles = $per->Roles()->get();
        $roless = Role::whereNotIn('id', $per->Roles()->pluck('id'))
            ->where('guard_name', 'web')
            ->get();

        return view('livewire.seguridad.permiso-roles', compact('per', 'titulo', 'roles', 'roless'));
    }
}
