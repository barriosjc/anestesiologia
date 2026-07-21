<?php

namespace App\Livewire\Presupuestos;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class PresupuestoIndex extends Component
{
    public function render()
    {
        return view('livewire.presupuestos.presupuesto-index');
    }
}
