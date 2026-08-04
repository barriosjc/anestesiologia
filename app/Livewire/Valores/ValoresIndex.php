<?php

namespace App\Livewire\Valores;

use App\Models\Nomenclador;
use App\Models\NomPracticasEstudio;
use App\Models\Valores;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
class ValoresIndex extends Component
{
    use WithPagination;

    public ?string $filtroGrupo = null;
    public ?string $filtroNivel = null;

    public ?int $modalValorId = null;
    public ?string $modalValor = null;

    public ?int $grupoC = null;
    public ?int $grupoN = null;
    public ?int $porcentaje = null;

    public ?string $nuevoGrupo = null;
    public ?string $nuevoNivel = null;
    public ?string $nuevoTipo = null;
    public ?string $nuevoValor = null;
    public string $nuevoMoneda = 'ARS';

    public function mount(): void
    {
        $this->filtroGrupo = request('grupo');
        $this->filtroNivel = request('nivel');
    }

    public function filtrar(): void
    {
        $this->resetPage();
    }

    public function limpiar(): void
    {
        $this->reset(['filtroGrupo', 'filtroNivel']);
        $this->resetPage();
    }

    public function abrirModalValor(int $id): void
    {
        $valores = Valores::withTrashed()->find($id);

        if ($valores) {
            $this->modalValorId = $id;
            $this->modalValor = number_format((float) $valores->valor, 2, ',', '.');
            $this->dispatch('open-modal', modal: 'valorModal');
        }
    }

    public function guardarValor(): void
    {
        $this->validate([
            'modalValor' => ['required'],
        ]);

        $valor = floatval(str_replace(',', '.', str_replace('.', '', $this->modalValor)));
        Valores::where('id', $this->modalValorId)->first()->update(['valor' => $valor]);

        $this->dispatch('close-modal', modal: 'valorModal');
        session()->flash('success', 'La operación se ha completado exitosamente.');
    }

    public function abrirModalNuevo(): void
    {
        $this->reset(['nuevoGrupo', 'nuevoNivel', 'nuevoTipo', 'nuevoValor', 'nuevoMoneda']);
        $this->nuevoMoneda = 'ARS';
        $this->dispatch('open-modal', modal: 'nuevoModal');
    }

    public function guardarNuevo(): void
    {
        $this->validate([
            'nuevoGrupo'  => ['required'],
            'nuevoNivel'  => [
                'required',
                Rule::unique('nom_valores')->where(fn ($q) => $q->where('grupo', $this->nuevoGrupo)),
            ],
            'nuevoTipo'   => ['required'],
            'nuevoValor'  => ['required'],
            'nuevoMoneda' => ['required'],
        ], [
            'nuevoNivel.unique' => 'El nivel ya existe para el grupo seleccionado.',
        ]);

        Valores::create([
            'grupo'  => $this->nuevoGrupo,
            'nivel'  => $this->nuevoNivel,
            'tipo'   => $this->nuevoTipo,
            'valor'  => $this->nuevoValor,
            'moneda' => $this->nuevoMoneda,
        ]);

        $this->dispatch('close-modal', modal: 'nuevoValorModal');
        $this->reset(['nuevoGrupo', 'nuevoNivel', 'nuevoTipo', 'nuevoValor', 'nuevoMoneda']);
        $this->nuevoMoneda = 'ARS';
        session()->flash('success', 'La operación se ha completado exitosamente.');
    }

    public function copiarGrupo(): void
    {
        $this->validate([
            'grupoC'     => ['required', 'integer', 'max:999', 'exists:nom_valores,grupo'],
            'grupoN'     => ['required', 'integer', 'max:999', 'unique:nom_valores,grupo'],
            'porcentaje' => ['required', 'integer', 'between:-99,100'],
        ], [
            'grupoC.required' => 'Debe ingresar el Numero entero de grupo que existe para copiar, tomado como plantilla.',
            'grupoC.integer'  => 'El grupo debe ser un número entero.',
            'grupoC.max'      => 'El grupo no puede ser mayor a 999.',
            'grupoC.exists'   => 'El grupo a copiar no se encuentra en ninguna lista de precios.',
            'grupoN.required' => 'Debe ingresar el Numero entero de grupo, el nro puede estar siendo usado.',
            'grupoN.integer'  => 'El nuevo grupo debe ser un número entero.',
            'grupoN.max'      => 'El nuevo grupo no puede ser mayor a 999.',
            'grupoN.unique'   => 'El nuevo grupo ya está asignado a una lista de precios existente.',
            'porcentaje.required' => 'Debe ingresar un valor de % aplicar entre -99 a 100.',
            'porcentaje.integer'  => 'El porcentaje debe ser un número entero.',
            'porcentaje.between'  => 'El porcentaje debe estar entre -99 y 100.',
        ]);

        $registros = Valores::where('grupo', $this->grupoC)->get();

        foreach ($registros as $registro) {
            Valores::create([
                'nivel'  => $registro->nivel,
                'valor'  => $registro->valor + ($this->porcentaje * $registro->valor / 100),
                'grupo'  => $this->grupoN,
                'tipo'   => $registro->tipo,
                'moneda' => $registro->moneda,
            ]);
        }

        $this->dispatch('close-modal', modal: 'nuevoModal');
        $this->reset(['grupoC', 'grupoN', 'porcentaje']);
        session()->flash('success', 'La operación se ha completado exitosamente.');
    }

    public function borrar(int $id): void
    {
        $valores = Valores::withTrashed()->findOrFail($id);

        if ($valores->trashed()) {
            $valores->restore();
            $mensaje = 'Registro restaurado con éxito.';
        } else {
            $valores->delete();
            $mensaje = 'Registro enviado a la papelera.';
        }

        session()->flash('message', $mensaje);
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $query = Valores::query()->withTrashed();

        if (!empty($this->filtroGrupo)) {
            $query->where('grupo', $this->filtroGrupo);
        }
        if (!empty($this->filtroNivel)) {
            $query->where('nivel', $this->filtroNivel);
        }

        $valores = $query->orderBy('nivel', 'asc')
            ->orderBy('created_at', 'asc')
            ->paginate();

        $niveles = Nomenclador::select('nivel')
            ->distinct()
            ->union(
                NomPracticasEstudio::select('codigo as nivel')
                    ->distinct()
            )
            ->get();

        return view('livewire.valores.valores-index', compact('valores', 'niveles'));
    }
}
