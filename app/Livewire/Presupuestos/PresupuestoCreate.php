<?php

namespace App\Livewire\Presupuestos;

use App\Models\Centro;
use App\Models\Gerenciadora;
use App\Models\Parametro;
use App\Models\PresupuestoCab;
use App\Models\Profesional;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class PresupuestoCreate extends Component
{
    public $presupuesto_id;
    public $gerenciadora_id;
    public $fecha;
    public $centro_id;
    public $profesional_id;
    public $valor_dolar;
    public $nombre;
    public $dni;
    public $fecha_nac;
    public $observaciones;

    public function mount($id = null)
    {
        $this->fecha = now()->format('Y-m-d');
        $this->valor_dolar = Parametro::where('nombre', 'UDS')->value('valor');

        if ($id) {
            $presupuesto = PresupuestoCab::find($id);

            if (!$presupuesto) {
                return redirect()->back()->with('error', 'No es posible editar un presupuesto dado de baja.');
            }

            $this->presupuesto_id = $presupuesto->id;
            $this->gerenciadora_id = $presupuesto->gerenciadora_id;
            $this->fecha = $presupuesto->fecha;
            $this->centro_id = $presupuesto->centro_id;
            $this->profesional_id = $presupuesto->profesional_id;
            $this->valor_dolar = $presupuesto->valor_dolar;
            $this->nombre = $presupuesto->nombre;
            $this->dni = $presupuesto->dni;
            $this->fecha_nac = $presupuesto->fecha_nac;
            $this->observaciones = $presupuesto->observaciones;
        }
    }

    public function save()
    {
        $this->validate();

        $presupuesto = PresupuestoCab::updateOrCreate(['id' => $this->presupuesto_id], [
            'fecha'          => $this->fecha,
            'nombre'         => $this->nombre,
            'fecha_nac'      => $this->fecha_nac,
            'dni'            => $this->dni,
            'centro_id'      => $this->centro_id,
            'observaciones'  => $this->observaciones,
            'usuario_id'     => Auth::id(),
            'profesional_id' => $this->profesional_id,
            'valor_dolar'    => $this->valor_dolar,
            'estado'         => 'I',
            'gerenciadora_id' => $this->gerenciadora_id,
        ]);

        session()->flash('success', "La operación se ha completado exitosamente, presupuesto nro: {$presupuesto->id}.");

        return $this->redirect(route('presupuestos.det.create', $presupuesto->id), navigate: true);
    }

    public function rules()
    {
        return [
            'fecha'          => ['required', 'date'],
            'nombre'         => ['required', 'string', 'max:255'],
            'fecha_nac'      => ['nullable', 'date'],
            'dni'            => ['nullable', 'string', 'max:20'],
            'centro_id'      => ['required', 'integer', 'exists:centros,id'],
            'gerenciadora_id' => ['required', 'integer'],
            'observaciones'  => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function render()
    {
        $gerenciadoras = Gerenciadora::all();
        $centros = Centro::all();
        $profesionales = Profesional::orderBy('nombre', 'asc')->get();

        return view('livewire.presupuestos.presupuesto-create', compact('gerenciadoras', 'centros', 'profesionales'));
    }
}
