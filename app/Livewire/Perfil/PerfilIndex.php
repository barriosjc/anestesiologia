<?php

namespace App\Livewire\Perfil;

use App\Models\user;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.main')]
class PerfilIndex extends Component
{
    use WithFileUploads;

    public int $userId;
    public ?string $name = null;
    public ?string $telefono = null;
    public ?string $email = null;
    public ?\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $foto = null;

    public function mount(int $id): void
    {
        $this->userId = $id;

        $user = user::findOrFail($id);
        $this->name = $user->name;
        $this->telefono = $user->telefono;
        $this->email = $user->email;
    }

    public function guardar(): void
    {
        $this->validate([
            'name'     => ['required', 'string', 'max:190'],
            'telefono' => ['nullable', 'string', 'max:45'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
        ], [
            'name.required'  => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email'    => 'El email debe ser una dirección válida.',
            'email.unique'   => 'El email ya está en uso.',
        ]);

        try {
            $user = user::findOrFail($this->userId);
            $user->name = $this->name;
            $user->telefono = $this->telefono;
            $user->email = $this->email;
            $user->save();
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo guardar los datos del usuario.');

            return;
        }

        session()->flash('success', 'Se guardaron los datos del usuario en forma correcta.');
    }

    public function guardarFoto(): void
    {
        $this->validate([
            'foto' => ['required', 'image'],
        ], [
            'foto.required' => 'Debe seleccionar una imagen.',
            'foto.image'    => 'El archivo debe ser una imagen (JPG o PNG).',
        ]);

        try {
            $user = user::findOrFail($this->userId);

            if (!empty($user->foto) && $user->foto !== 'fotovacia.jpeg') {
                Storage::disk('usuarios')->delete($user->foto);
            }

            $path = Storage::disk('usuarios')->put('', $this->foto);
            $user->foto = $path;
            $user->save();
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo actualizar la foto.');

            return;
        }

        $this->reset('foto');
        session()->flash('success', 'Foto actualizada correctamente.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $user = user::findOrFail($this->userId);

        return view('livewire.perfil.perfil-index', compact('user'));
    }
}
