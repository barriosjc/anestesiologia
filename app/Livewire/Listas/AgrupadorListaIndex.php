<?php

namespace App\Livewire\Listas;

use App\Models\Centro;
use App\Models\Cobertura;
use App\Models\Gerenciadora;
use App\Models\Periodo;
use App\Models\Valores_cab;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class AgrupadorListaIndex extends Component
{
    use WithPagination;

    public ?int $filtroGerenciadoraId = null;
    public ?int $filtroCoberturaId = null;
    public ?int $filtroCentroId = null;
    public ?string $filtroPeriodo = null;
    public ?int $filtroGrupo = null;

    public ?int $listaId = null;
    public ?int $gerenciadora_id = null;
    public ?int $cobertura_id = null;
    public ?int $centro_id = null;
    public ?string $periodo = null;
    public ?int $grupo = null;

    public function mount(int $nom_padre): void
    {
        session(['ses_nom_padre_id' => $nom_padre]);
        $this->filtroGerenciadoraId = request('gerenciadora_id');
        $this->filtroCoberturaId = request('cobertura_id');
        $this->filtroCentroId = request('centro_id');
        $this->filtroPeriodo = request('periodo');
        $this->filtroGrupo = request('grupo');
    }

    public function filtrar(): void
    {
        $this->resetPage();
    }

    public function limpiar(): void
    {
        $this->reset(['filtroGerenciadoraId', 'filtroCoberturaId', 'filtroCentroId', 'filtroPeriodo', 'filtroGrupo']);
        $this->resetPage();
    }

    public function abrirModal(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->reset(['listaId', 'gerenciadora_id', 'cobertura_id', 'centro_id', 'periodo', 'grupo']);

        if ($id) {
            $lista = Valores_cab::findOrFail($id);
            $this->listaId = $lista->id;
            $this->gerenciadora_id = $lista->gerenciadora_id;
            $this->cobertura_id = $lista->cobertura_id;
            $this->centro_id = $lista->centro_id;
            $this->periodo = $lista->periodo;
            $this->grupo = $lista->grupo;
        }

        $this->dispatch('open-modal', modal: 'listaModal');
    }

    public function guardar(): void
    {
        $this->validate([
            'cobertura_id' => 'required|integer',
            'centro_id'    => 'required|integer',
            'periodo'      => 'required|string|max:10',
            'grupo'        => 'required|integer|min:1|max:1000',
            'gerenciadora_id' => [
                'required',
                'integer',
                Rule::unique('nom_valores_cab')
                    ->where(fn ($q) => $q->where('cobertura_id', $this->cobertura_id)
                        ->where('centro_id', $this->centro_id)
                        ->where('periodo', $this->periodo))
                    ->ignore($this->listaId),
            ],
        ], [
            'gerenciadora_id.unique' => 'Los datos ingresados para la lista de precios ya tiene un grupo asignado.',
        ]);

        try {
            $lista = $this->listaId ? Valores_cab::find($this->listaId) : new Valores_cab();
            $lista->fill($this->only(['gerenciadora_id', 'cobertura_id', 'centro_id', 'periodo', 'grupo']));
            $lista->save();
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo guardar la lista. Verifique los datos e intente nuevamente.');

            return;
        }

        $this->dispatch('close-modal', modal: 'listaModal');
        session()->flash('success', 'La operación se ha completado exitosamente.');
    }

    public function borrar(int $id): void
    {
        try {
            $lista = Valores_cab::find($id);

            if (!$lista) {
                session()->flash('error', 'La lista no existe o ya fue eliminada.');

                return;
            }

            $lista->delete();
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo eliminar la lista.');

            return;
        }

        session()->flash('success', 'La operación se ha completado exitosamente.');
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $nomPadreId = session('ses_nom_padre_id');

        $query = Valores_cab::with(['gerenciadora:id,nombre', 'cobertura:id,sigla', 'centro:id,nombre'])
            ->whereHas('gerenciadora')
            ->whereHas('cobertura', function ($q) use ($nomPadreId) {
                if (!empty($nomPadreId)) {
                    $q->where('nom_padre_id', $nomPadreId);
                }
            })
            ->whereHas('centro');

        if (!empty($this->filtroGerenciadoraId)) {
            $query->where('gerenciadora_id', $this->filtroGerenciadoraId);
        }
        if (!empty($this->filtroCoberturaId)) {
            $query->where('cobertura_id', $this->filtroCoberturaId);
        }
        if (!empty($this->filtroCentroId)) {
            $query->where('centro_id', $this->filtroCentroId);
        }
        if (!empty($this->filtroPeriodo)) {
            $query->where('periodo', $this->filtroPeriodo);
        }
        if (!empty($this->filtroGrupo)) {
            $query->where('grupo', $this->filtroGrupo);
        }

        $listas = $query->paginate();

        $gerenciadoras = Gerenciadora::orderBy('nombre')->get();
        $coberturas = Cobertura::orderBy('nombre')->get();
        $centros = Centro::orderBy('nombre')->get();
        $periodos = Periodo::orderBy('nombre')->get();

        return view('livewire.listas.agrupador-lista-index', compact(
            'listas',
            'gerenciadoras',
            'coberturas',
            'centros',
            'periodos'
        ));
    }
}
