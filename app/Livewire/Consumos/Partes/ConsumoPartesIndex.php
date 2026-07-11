<?php

namespace App\Livewire\Consumos\Partes;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class ConsumoPartesIndex extends Component
{
    public function render()
    {
        return view('livewire.consumos.partes.consumo-partes-index');
    }
}
