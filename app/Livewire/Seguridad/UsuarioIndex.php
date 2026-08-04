<?php

namespace App\Livewire\Seguridad;

use App\Models\Centro;
use App\Models\Role;
use App\Models\user;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class UsuarioIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $modalId = null;
    public ?string $name = null;
    public ?string $email = null;
    public ?int $centro_id = null;
    public array $perfiles = [];
    public bool $blanquear = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function abrirModalNuevo(): void
    {
        $this->reset(['modalId', 'name', 'email', 'centro_id', 'perfiles', 'blanquear']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'usuarioModal');
    }

    public function abrirModalEditar(int $id): void
    {
        $user = user::findOrFail($id);

        $this->modalId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->centro_id = $user->centro_id;
        $this->perfiles = $user->roles->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->blanquear = false;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'usuarioModal');
    }

    public function guardar(): void
    {
        $this->validate([
            'name'      => ['required', 'string', 'max:50'],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->modalId)],
            'centro_id' => ['required'],
            'perfiles'  => ['required', 'array', 'min:1'],
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'name.max'           => 'El nombre no puede superar los 50 caracteres.',
            'email.required'     => 'El email es obligatorio.',
            'email.email'        => 'El email debe ser una dirección válida.',
            'email.unique'       => 'El email ya está en uso.',
            'centro_id.required' => 'Debe seleccionar un centro.',
            'perfiles.required'  => 'Debe asignar al menos un perfil.',
            'perfiles.min'       => 'Debe asignar al menos un perfil.',
        ]);

        if ($this->modalId) {
            $user = user::findOrFail($this->modalId);
            $user->name = $this->name;
            $user->email = $this->email;
            $user->centro_id = $this->centro_id;

            if ($this->blanquear) {
                $user->password = Hash::make('12345678');
                $user->cambio_password = 1;
            }

            $user->save();
            $user->syncRoles($this->perfiles);
        } else {
            $user = user::create([
                'name'            => $this->name,
                'email'           => $this->email,
                'centro_id'       => $this->centro_id,
                'password'        => Hash::make('12345678'),
                'cambio_password' => 1,
                'foto'            => 'fotovacia.jpeg',
            ]);
            $user->assignRole($this->perfiles);
        }

        $this->dispatch('close-modal', modal: 'usuarioModal');
        $this->reset(['modalId', 'name', 'email', 'centro_id', 'perfiles', 'blanquear']);
        session()->flash('success', 'Se guardó los datos del usuario de forma correcta.');
    }

    public function borrar(int $id): void
    {
        user::destroy($id);
        session()->flash('success', 'Usuario borrado!');
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $users = user::query()
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'LIKE', "%{$this->search}%")
                    ->orWhere('email', 'LIKE', "%{$this->search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        $centros = Centro::orderBy('nombre')->get();
        $roles = Role::orderBy('name')->get();

        return view('livewire.seguridad.usuario-index', compact('users', 'centros', 'roles'));
    }
}
