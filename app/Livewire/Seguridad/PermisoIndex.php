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
        $this->dispatch('open-modal', modal: 'permisoModal');
    }

    public function abrirModalEditar(int $id): void
    {
        $permiso = Permission::findOrFail($id);

        $this->modalId = $permiso->id;
        $this->name = $permiso->name;
        $this->guard_name = $permiso->guard_name;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'permisoModal');
    }

    public function guardar(): void
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

    public function borrar(int $id): void
    {
        Permission::findOrFail($id)->delete();
        session()->flash('success', 'Permiso borrado correctamente.');
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
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
