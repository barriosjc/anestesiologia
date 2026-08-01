<?php

namespace App\Livewire\Presupuestos;

use App\Models\PresupuestoCab;
use App\Models\PresupuestoPago;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class PresupuestoPagoIndex extends Component
{
    public $presupuestoCabId;
    public $fecha;
    public $valor = '0,00';
    public $observaciones;

    public function mount($id)
    {
        $this->presupuestoCabId = $id;
        $this->fecha = now()->format('Y-m-d');
    }

    public function guardar()
    {
        $this->validate([
            'fecha' => ['required', 'date'],
            'valor' => ['required'],
        ]);

        PresupuestoPago::create([
            'presupuesto_cab_id' => $this->presupuestoCabId,
            'fecha'              => $this->fecha,
            'valor'              => $this->valor,
            'usuario_id'         => Auth::id(),
        ]);

        $this->reset(['valor', 'observaciones']);
        $this->fecha = now()->format('Y-m-d');
        $this->valor = '0,00';

        session()->flash('success', 'La operación se ha completado exitosamente.');
    }

    public function destroy($id)
    {
        PresupuestoPago::findOrFail($id)->delete();

        session()->flash('success', 'El pago ha sido eliminado exitosamente.');
    }

    public function render()
    {
        $presupuestosCab = PresupuestoCab::findOrFail($this->presupuestoCabId);
        $pagos = PresupuestoPago::where('presupuesto_cab_id', $this->presupuestoCabId)
            ->with('user')
            ->orderBy('id')
            ->get();

        return view('livewire.presupuestos.presupuesto-pago-index', compact('presupuestosCab', 'pagos'));
    }
}
