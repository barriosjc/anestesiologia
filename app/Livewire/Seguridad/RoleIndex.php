<?php

namespace App\Livewire\Seguridad;

use App\Models\Role;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class RoleIndex extends Component
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
        $this->dispatch('open-modal', modal: 'roleModal');
    }

    public function abrirModalEditar($id)
    {
        $role = Role::findOrFail($id);

        $this->modalId = $role->id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'roleModal');
    }

    public function guardar()
    {
        $this->validate([
            'name'       => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->modalId)],
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
            Role::findOrFail($this->modalId)->update($data);
        } else {
            Role::create($data);
        }

        $this->dispatch('close-modal', modal: 'roleModal');
        $this->reset(['modalId', 'name', 'guard_name']);
        session()->flash('success', 'Role guardado correctamente.');
    }

    public function borrar($id)
    {
        Role::findOrFail($id)->delete();
        session()->flash('success', 'Role borrado correctamente.');
    }

    public function paginationView()
    {
        return 'livewire::bootstrap';
    }

    public function render()
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'LIKE', "%{$this->search}%")
                    ->orWhere('guard_name', 'LIKE', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.seguridad.role-index', compact('roles'));
    }
}
