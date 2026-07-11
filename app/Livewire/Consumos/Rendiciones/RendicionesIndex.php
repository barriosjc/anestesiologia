<?php

namespace App\Livewire\Consumos\Rendiciones;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class RendicionesIndex extends Component
{
    public function render()
    {
        return view('livewire.consumos.rendiciones.rendiciones-index');
    }
}
