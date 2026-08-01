<?php

namespace App\Livewire\Seguridad;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

#[Layout('layouts.main')]
class PermisoIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $modalId;
    public $name;
    public $guard_name;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function abrirModalNuevo()
    {
        $this->reset(['modalId', 'name', 'guard_name']);
        $this->guard_name = 'web';
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'permisoModal');
    }

    public function abrirModalEditar($id)
    {
        $permiso = Permission::findOrFail($id);

        $this->modalId = $permiso->id;
        $this->name = $permiso->name;
        $this->guard_name = $permiso->guard_name;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'permisoModal');
    }

    public function guardar()
    {
        $this->validate([
            'name'       => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($this->modalId)],
            'guard_name' => ['required', 'string', 'max:255'],
        ], [
            'name.required'       => 'El nombre es obligatorio.',
            'name.unique'         => 'El nombre ya está en uso.',
            'guard_name.required' => 'El guard name es obligatorio.',
        ]);

        $data = [
            'name'       => $this->name,
            'guard_name' => $this->guard_name,
        ];

        if ($this->modalId) {
            Permission::findOrFail($this->modalId)->update($data);
        } else {
            Permission::create($data);
        }

        $this->dispatch('close-modal', modal: 'permisoModal');
        $this->reset(['modalId', 'name', 'guard_name']);
        session()->flash('success', 'Permiso guardado correctamente.');
    }

    public function borrar($id)
    {
        Permission::findOrFail($id)->delete();
        session()->flash('success', 'Permiso borrado correctamente.');
    }

    public function paginationView()
    {
        return 'livewire::bootstrap';
    }

    public function render()
    {
        $permisos = Permission::query()
            ->where('guard_name', 'web')
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'LIKE', "%{$this->search}%")
                    ->orWhere('guard_name', 'LIKE', "%{$this->search}%");
            })
            ->orderByDesc('id')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.seguridad.permiso-index', compact('permisos'));
    }
}
