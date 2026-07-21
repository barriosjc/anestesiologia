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
    public $presupuestoCabId;

    public function mount($id)
    {
        $this->presupuestoCabId = $id;
    }

    #[On('presupuesto-det-guardado')]
    public function onPresupuestoDetGuardado()
    {
        //
    }

    public function destroy($id)
    {
        PresupuestoDet::findOrFail($id)->delete();

        session()->flash('success', 'Detalle de presupuesto eliminado correctamente.');
    }

    public function render(PresupuestoDetalleRepository $repository)
    {
        $presupuestosCab = PresupuestoCab::findOrFail($this->presupuestoCabId);
        $presupuestosDet = $repository->detalleConDescripcion($this->presupuestoCabId);

        return view('livewire.presupuestos.presupuesto-det-index', compact(
            'presupuestosCab',
            'presupuestosDet'
        ));
    }
}
