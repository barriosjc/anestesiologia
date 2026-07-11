<?php

namespace App\Livewire\Consumos\Cargar;

use App\Models\Consumo_cab;
use App\Models\Consumo_det;
use App\Models\Paciente;
use App\Models\Parte_cab;
use App\Repositories\ConsumoRepository;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ConsumoCargarForm extends Component
{
    public $parteCabId;
    public $gerenciadoraId;
    public $coberturaId;
    public $nomPadreJson;
    public $soloConsulta;

    public $periodo;
    public $nomencladorOpciones = [];
    public $nomenclador_id;
    public $nom_padre_id;
    public $porcentaje = 100;
    public $valorOrig = 0;
    public $valorTotal = 0;

    public function mount($parteCabId, $gerenciadoraId, $coberturaId, $nomPadreJson, $soloConsulta)
    {
        $this->parteCabId = $parteCabId;
        $this->gerenciadoraId = $gerenciadoraId;
        $this->coberturaId = $coberturaId;
        $this->nomPadreJson = $nomPadreJson;
        $this->soloConsulta = $soloConsulta;
    }

    public function buscarNomenclador($codigo, $descripcion, ConsumoRepository $consumoRepository, \App\Services\NomencladoresServices $nomencladoresServices)
    {
        if (empty($codigo) && empty($descripcion)) {
            return;
        }

        $termino = !empty($codigo) ? $codigo : $descripcion;

        $this->nomencladorOpciones = $nomencladoresServices->buscar(
            $termino,
            $this->nomPadreJson,
            $this->gerenciadoraId,
            $this->coberturaId
        );

        $this->nomenclador_id = null;
        $this->nom_padre_id = null;

        if (count($this->nomencladorOpciones) === 1) {
            $this->nomenclador_id = $this->nomencladorOpciones[0]['id'];
            $this->valorizar($consumoRepository);
        }
    }

    public function updatedPeriodo()
    {
        if ($this->nomenclador_id) {
            $this->valorizar(app(ConsumoRepository::class));
        }
    }

    public function updatedNomencladorId()
    {
        $this->valorizar(app(ConsumoRepository::class));
    }

    protected function valorizar(ConsumoRepository $consumoRepository)
    {
        if (empty($this->periodo) || empty($this->nomenclador_id)) {
            return;
        }

        $opcion = collect($this->nomencladorOpciones)->firstWhere('id', $this->nomenclador_id);
        if (!$opcion) {
            return;
        }

        $this->nom_padre_id = $opcion['nom_padre_id'];

        $parteCab = $consumoRepository->parteBuscar($this->parteCabId);
        $cobertura = $consumoRepository->coberturaBuscar($parteCab->cobertura_id);
        $valores = $consumoRepository->valorBuscar($this->periodo, $opcion['nivel'], $parteCab);

        if (empty($valores)) {
            $this->valorOrig = 0;
            return;
        }

        $porcentajeAdic = 0;
        if (!empty($valores->aplica_pocent_adic)) {
            $paciente = Paciente::where('id', $parteCab->paciente_id)->first();
            $fechaNacimiento = new DateTime($paciente->fec_nacimiento);
            $fechaActual = new DateTime('today');
            $edad = $fechaActual->diff($fechaNacimiento)->y;
            if (($edad < $cobertura->edad_hasta && $cobertura->edad_hasta != null) ||
                ($edad > $cobertura->edad_desde && $cobertura->edad_desde != null)) {
                $porcentajeAdic = $cobertura->porcentaje_adic;
            }
        }

        $this->valorOrig = $valores->valor;
        $this->porcentaje = ($this->porcentaje ?: 100) + $porcentajeAdic;
    }

    public function guardar()
    {
        $this->validate([
            'porcentaje' => 'required|numeric|between:1,200',
            'valorTotal' => 'required|numeric|gt:0',
            'nomenclador_id' => 'required',
            'nom_padre_id' => 'required',
        ]);

        $parte = Parte_cab::find($this->parteCabId);
        $parte->estado_id = 4; // en facturacion
        $parte->save();

        $consumoCab = Consumo_cab::where('parte_cab_id', $this->parteCabId)->first();
        if (empty($consumoCab)) {
            $consumoCab = new Consumo_cab();
            $consumoCab->parte_cab_id = $this->parteCabId;
            $consumoCab->user_id = Auth::user()->id;
            $consumoCab->save();
        }

        $consumoDet = new Consumo_det();
        $consumoDet->consumo_cab_id = $consumoCab->id;
        $consumoDet->nom_padre_id = $this->nom_padre_id;
        $consumoDet->nomenclador_id = $this->nomenclador_id;
        $consumoDet->porcentaje = $this->porcentaje;
        $consumoDet->cantidad = 1;
        $consumoDet->valor = $this->valorTotal;
        $consumoDet->estado_id = 4; // en facturacion
        $consumoDet->save();

        $this->reset(['nomencladorOpciones', 'nomenclador_id', 'nom_padre_id', 'porcentaje', 'valorOrig', 'valorTotal']);

        session()->flash('success', 'Consumo cargado correctamente.');

        $this->dispatch('consumo-guardado');
    }

    public function render()
    {
        return view('livewire.consumos.cargar.consumo-cargar-form', [
            'periodos' => \App\Models\Periodo::orderBy('nombre')->get(),
        ]);
    }
}
