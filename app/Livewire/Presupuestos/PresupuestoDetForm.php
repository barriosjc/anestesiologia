<?php

namespace App\Livewire\Presupuestos;

use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use App\Repositories\PresupuestoDetalleRepository;
use App\Services\NomencladoresServices;
use Livewire\Component;

class PresupuestoDetForm extends Component
{
    public int $presupuestoCabId;
    public ?int $gerenciadoraId = null;
    public ?int $centroId = null;
    public ?float $valorDolar = null;

    public ?int $coberturaId = null;
    public ?string $periodo = null;
    public array $nomencladorOpciones = [];
    public ?int $nomenclador_id = null;
    public ?int $nom_padre_id = null;
    public $porcentaje = 100;
    public $valorOrig = 0;
    public $valorTotal = 0;
    public ?string $observaciones = null;

    public function mount(int $presupuestoCabId): void
    {
        $presupuestoCab = PresupuestoCab::findOrFail($presupuestoCabId);

        $this->presupuestoCabId = $presupuestoCabId;
        $this->gerenciadoraId = $presupuestoCab->gerenciadora_id;
        $this->centroId = $presupuestoCab->centro_id;
        $this->valorDolar = $presupuestoCab->valor_dolar;
    }

    public function buscarNomenclador(string $codigo, string $descripcion, NomencladoresServices $nomencladoresServices): void
    {
        if (empty($codigo) && empty($descripcion)) {
            return;
        }

        $termino = !empty($codigo) ? $codigo : $descripcion;

        $this->nomencladorOpciones = $nomencladoresServices->buscar(
            $termino,
            null,
            $this->gerenciadoraId,
            $this->coberturaId
        );

        $this->nomenclador_id = null;
        $this->nom_padre_id = null;

        if (count($this->nomencladorOpciones) === 1) {
            $this->nomenclador_id = $this->nomencladorOpciones[0]['id'];
            $this->valorizar(app(PresupuestoDetalleRepository::class));
        }
    }

    public function updatedPeriodo(): void
    {
        if ($this->nomenclador_id) {
            $this->valorizar(app(PresupuestoDetalleRepository::class));
        }
    }

    public function updatedNomencladorId(): void
    {
        $this->valorizar(app(PresupuestoDetalleRepository::class));
    }

    protected function valorizar(PresupuestoDetalleRepository $repository): void
    {
        if (empty($this->periodo) || empty($this->nomenclador_id) || empty($this->coberturaId)) {
            return;
        }

        $opcion = collect($this->nomencladorOpciones)->firstWhere('id', $this->nomenclador_id);
        if (!$opcion) {
            return;
        }

        $this->nom_padre_id = $opcion['nom_padre_id'];

        $valores = $repository->buscarValor(
            $this->gerenciadoraId,
            $this->coberturaId,
            $this->centroId,
            $this->periodo,
            $opcion['nivel']
        );

        if (empty($valores)) {
            $this->valorOrig = 0;
            return;
        }

        $valor = (float) $valores->valor;
        if (($valores->moneda ?? 'ARS') === 'USD') {
            $valor = round($valor * ($this->valorDolar ?: 1), 2);
        }

        $this->valorOrig = $valor;
        $this->porcentaje = 100;
    }

    public function guardar(): void
    {
        $this->validate([
            'coberturaId' => 'required',
            'periodo' => 'required',
            'nomenclador_id' => 'required',
            'nom_padre_id' => 'required',
            'porcentaje' => 'required|numeric|min:1|max:200',
            'valorTotal' => 'required|numeric|gt:0',
        ]);

        $det = new PresupuestoDet();
        $det->presupuesto_cab_id = $this->presupuestoCabId;
        $det->cobertura_id = $this->coberturaId;
        $det->nom_padre_id = $this->nom_padre_id;
        $det->nomenclador_id = $this->nomenclador_id;
        $det->porcentaje = $this->porcentaje;
        $det->valor = $this->valorTotal;
        $det->periodo = $this->periodo;
        $det->observaciones = $this->observaciones;
        $det->save();

        $this->reset([
            'coberturaId', 'periodo', 'nomencladorOpciones', 'nomenclador_id',
            'nom_padre_id', 'porcentaje', 'valorOrig', 'valorTotal', 'observaciones',
        ]);

        session()->flash('success', 'Práctica cargada correctamente.');

        $this->dispatch('presupuesto-det-guardado');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.presupuestos.presupuesto-det-form', [
            'coberturas' => \App\Models\Cobertura::orderBy('nombre')->get(),
            'periodos' => \App\Models\Periodo::orderBy('nombre')->get(),
        ]);
    }
}
