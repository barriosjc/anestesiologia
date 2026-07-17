<?php

namespace App\Livewire\Cargas;

use App\Repositories\CalendarRepository;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class CalendarIndex extends Component
{
    public $fecha;
    public $observaciones;
    public $cerrado = false;
    public $cancelar = false;

    public function guardar(CalendarRepository $calendarRepository)
    {
        $resultado = $calendarRepository->guardar([
            'fecha' => $this->fecha,
            'observaciones' => $this->observaciones,
            'cerrado' => $this->cerrado,
            'cancelar' => $this->cancelar,
        ]);

        if (!$resultado['success']) {
            $this->addError('fecha', $resultado['mensaje']);
            return;
        }

        $this->reset(['fecha', 'observaciones', 'cerrado', 'cancelar']);

        $this->dispatch('calendario-guardado', eventos: $calendarRepository->eventos());
    }

    public function render(CalendarRepository $calendarRepository)
    {
        return view('livewire.cargas.calendar-index', [
            'eventos' => $calendarRepository->eventos(),
        ]);
    }
}
