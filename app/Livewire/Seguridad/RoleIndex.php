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

    public string $search = '';
    public ?int $modalId = null;
    public ?string $name = null;
    public ?string $guard_name = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function abrirModalNuevo(): void
    {
        $this->reset(['modalId', 'name', 'guard_name']);
        $this->guard_name = 'web';
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'roleModal');
    }

    public function abrirModalEditar(int $id): void
    {
        $role = Role::findOrFail($id);

        $this->modalId = $role->id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'roleModal');
    }

    public function guardar(): void
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

    public function borrar(int $id): void
    {
        Role::findOrFail($id)->delete();
        session()->flash('success', 'Role borrado correctamente.');
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
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
