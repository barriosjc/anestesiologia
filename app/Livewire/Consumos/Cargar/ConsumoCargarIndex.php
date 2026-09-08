<?php

namespace App\Livewire\Consumos\Cargar;

use App\Models\Documento;
use App\Models\Estado;
use App\Models\Consumo_det;
use App\Models\GerenciadoraCoberturaNomPadre;
use App\Models\Parte_cab;
use App\Models\Parte_det;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.main')]
class ConsumoCargarIndex extends Component
{
    public int $parte_cab_id;

    public ?int $estado_cambio = null;

    public string $observaciones = '';

    public function mount(int $id): void
    {
        $this->parte_cab_id = $id;

        $data = DB::table('v_parte_cab')->find($id);
        $this->estado_cambio = (int) $data->estado_id;
        $this->observaciones = $data->observacion ?? '';
    }

    #[On('consumo-guardado')]
    public function onConsumoGuardado(): void
    {
        //
    }

    public function destroy(int $id): void
    {
        Consumo_det::find($id)->delete();
    }

    public function observar(): void
    {
        $this->validate([
            'estado_cambio' => ['required'],
            'observaciones' => ['required', 'max:255'],
        ]);

        try {
            $parte = Parte_cab::find($this->parte_cab_id);
            $parte->observaciones = strip_tags($this->observaciones);
            $parte->estado_id = $this->estado_cambio;
            $parte->save();

            session()->flash('success', 'Estado cambiado exitosamente.');
            $this->redirect(route('consumos.partes.filtrar'));
        } catch (\Exception $e) {
            $this->addError('estado_cambio', $e->getMessage());
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $partes_det = Parte_det::where('parte_cab_id', $this->parte_cab_id)->paginate(3);
        $documentos = Documento::where('tipo', 'like', '%parte%')->get();
        $consumos = DB::table('v_consumos')->where('parte_cab_id', $this->parte_cab_id)->get();
        $data = DB::table('v_parte_cab')->find($this->parte_cab_id);
        $soloConsulta = !in_array(Parte_cab::find($this->parte_cab_id)->estado_id, [3, 4]);
        $array = GerenciadoraCoberturaNomPadre::where('gerenciadora_id', $data->gerenciadora_id)
            ->where('cobertura_id', $data->cobertura_id)
            ->pluck('nom_padre_id')
            ->toArray();
        $nom_padre_json = json_encode($array);
        $estados = Estado::get();

        return view('livewire.consumos.cargar.consumo-cargar-index', compact(
            'partes_det',
            'documentos',
            'consumos',
            'data',
            'soloConsulta',
            'nom_padre_json',
            'estados'
        ));
    }
}
