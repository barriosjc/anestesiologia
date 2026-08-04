<?php

namespace App\Livewire\Presupuestos;

use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use App\Repositories\PresupuestoDetalleRepository;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.main')]
class PresupuestoDetIndex extends Component
{
    public int $presupuestoCabId;

    public function mount(int $id): void
    {
        $this->presupuestoCabId = $id;
    }

    #[On('presupuesto-det-guardado')]
    public function onPresupuestoDetGuardado(): void
    {
        //
    }

    public function destroy(int $id): void
    {
        PresupuestoDet::findOrFail($id)->delete();

        session()->flash('success', 'Detalle de presupuesto eliminado correctamente.');
    }

    public function generarPartes(): void
    {
        $result = app(\App\Services\PresupuestoParteService::class)->generar($this->presupuestoCabId);

        if ($result['success']) {
            session()->flash('success', $result['message']);

            return;
        }

        if (! empty($result['message'])) {
            $this->addError('generar', $result['message']);
        }
        foreach ($result['errors'] as $error) {
            $this->addError('generar', $error);
        }
    }

    public function render(PresupuestoDetalleRepository $repository): \Illuminate\Contracts\View\View
    {
        $presupuestosCab = PresupuestoCab::findOrFail($this->presupuestoCabId);
        $presupuestosDet = $repository->detalleConDescripcion($this->presupuestoCabId);

        return view('livewire.presupuestos.presupuesto-det-index', compact(
            'presupuestosCab',
            'presupuestosDet'
        ));
    }
}
