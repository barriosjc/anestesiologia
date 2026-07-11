<?php

namespace App\Livewire\Partes;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class ParteIndex extends Component
{
    public function render()
    {
        return view('livewire.partes.parte-index');
    }
}
