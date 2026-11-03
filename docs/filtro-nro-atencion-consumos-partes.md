# Filtro "Nro atención" en Consumos → Partes

Pantalla: `/consumos/partes/filtrar` (`ConsumoPartesIndex`).

## Qué se hizo

- Se agregó el campo **Nro atención** en la **primera fila** del filtro.
- Para que entren los 6 campos, la primera fila pasó de `3 + 3 + 2 + 2 + 2` a `2 + 2 + 2 + 2 + 2 + 2` columnas (suma 12, sin desbordes).
- La segunda fila (fechas, paciente, botones) quedó igual.
- El valor se guarda en sesión con la clave `a_atencion`, se limpia con el botón de limpiar y se aplica al listado.
- La vista `v_parte_cab` **no** expone la columna `atencion`, por eso el filtro se resuelve con un subquery sobre `partes_cab` (mismo criterio que ya usa `ParteTabla`), con `LIKE %valor%`.
- Al pasar Coberturas de `col-md-3` a `col-md-2`, el desplegable de Choices.js quedó angosto y las siglas largas se cortaban en dos líneas. Se agregó la clase `choices-wide-dropdown` (nueva regla en `public/css/custom.css`) para ensanchar el desplegable y mostrar cada opción en una sola línea.

## Archivos modificados

1. `app/Livewire/Consumos/Partes/ConsumoPartesFiltro.php`
2. `app/Livewire/Consumos/Partes/ConsumoPartesTabla.php`
3. `resources/views/livewire/consumos/partes/consumo-partes-filtro.blade.php`
4. `public/css/custom.css`

---

## `app/Livewire/Consumos/Partes/ConsumoPartesFiltro.php`

```php
<?php

namespace App\Livewire\Consumos\Partes;

use App\Models\Centro;
use App\Models\Cobertura;
use App\Models\Estado;
use App\Models\Profesional;
use Livewire\Component;

class ConsumoPartesFiltro extends Component
{
    public ?int $cobertura_id = null;
    public ?int $centro_id = null;
    public ?int $profesional_id = null;
    public ?string $nombre = null;
    public ?string $nro_parte = null;
    public ?string $atencion = null;
    public ?string $fec_desde = null;
    public ?string $fec_hasta = null;
    public array $estado_id = [];
    public ?string $fec_desde_adm = null;
    public ?string $fec_hasta_adm = null;

    public function mount(): void
    {
        $this->cobertura_id = session('a_cobertura_id');
        $this->centro_id = session('a_centro_id');
        $this->profesional_id = session('a_profesional_id');
        $this->nombre = session('a_nombre');
        $this->nro_parte = session('a_nro_parte');
        $this->atencion = session('a_atencion');
        $this->fec_desde = session('a_fec_desde');
        $this->fec_hasta = session('a_fec_hasta');
        $this->estado_id = (array) session('a_estado_id', []);
        $this->fec_desde_adm = session('a_fec_desde_adm');
        $this->fec_hasta_adm = session('a_fec_hasta_adm');
    }

    protected function filtroKeys(): array
    {
        return [
            'cobertura_id', 'centro_id', 'profesional_id',
            'nombre', 'nro_parte', 'atencion', 'fec_desde', 'fec_hasta',
            'estado_id', 'fec_desde_adm', 'fec_hasta_adm',
        ];
    }

    public function aplicar(): void
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $filtros[$key] = $this->$key;
            session()->put('a_' . $key, $this->$key);
        }

        $this->dispatch('filtros-aplicados', filtros: $filtros);
    }

    public function limpiar(): void
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $this->$key = $key === 'estado_id' ? [] : null;
            $filtros[$key] = $this->$key;
            session()->forget('a_' . $key);
        }

        $this->dispatch('filtros-aplicados', filtros: $filtros);
        $this->dispatch('filtro-limpiado');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.consumos.partes.consumo-partes-filtro', [
            'coberturas' => Cobertura::orderBy('nombre')->get(),
            'centros' => Centro::orderBy('nombre')->get(),
            'profesionales' => Profesional::orderBy('nombre')->get(),
            'estados' => Estado::get(),
        ]);
    }
}
```

---

## `app/Livewire/Consumos/Partes/ConsumoPartesTabla.php`

```php
<?php

namespace App\Livewire\Consumos\Partes;

use App\Models\ParteCab;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ConsumoPartesTabla extends Component
{
    use WithPagination;

    public array $filtros = [];

    public function mount(): void
    {
        $this->filtros = [
            'cobertura_id' => session('a_cobertura_id'),
            'centro_id' => session('a_centro_id'),
            'profesional_id' => session('a_profesional_id'),
            'nombre' => session('a_nombre'),
            'nro_parte' => session('a_nro_parte'),
            'atencion' => session('a_atencion'),
            'fec_desde' => session('a_fec_desde'),
            'fec_hasta' => session('a_fec_hasta'),
            'estado_id' => (array) session('a_estado_id', []),
            'fec_desde_adm' => session('a_fec_desde_adm'),
            'fec_hasta_adm' => session('a_fec_hasta_adm'),
        ];
    }

    #[On('filtros-aplicados')]
    public function aplicarFiltros(array $filtros): void
    {
        $this->filtros = $filtros;
        $this->resetPage();
    }

    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $query = ParteCab::vParteCab();

        if (!empty($this->filtros['cobertura_id'])) {
            $query->where('cobertura_id', '=', $this->filtros['cobertura_id']);
        }
        if (!empty($this->filtros['centro_id'])) {
            $query->where('centro_id', '=', $this->filtros['centro_id']);
        }
        if (!empty($this->filtros['profesional_id'])) {
            $query->where('profesional_id', '=', $this->filtros['profesional_id']);
        }
        if (!empty($this->filtros['estado_id'])) {
            $query->whereIn('estado_id', (array) $this->filtros['estado_id']);
        }
        if (!empty($this->filtros['nombre'])) {
            $query->where('paciente', 'like', '%' . $this->filtros['nombre'] . '%');
        }
        if (!empty($this->filtros['fec_desde'])) {
            $query->where('fec_prestacion_orig', '>=', $this->filtros['fec_desde']);
        }
        if (!empty($this->filtros['fec_hasta'])) {
            $query->where('fec_prestacion_orig', '<=', $this->filtros['fec_hasta']);
        }
        if (!empty($this->filtros['fec_desde_adm'])) {
            $query->where('created_at', '>=', $this->filtros['fec_desde_adm']);
        }
        if (!empty($this->filtros['fec_hasta_adm'])) {
            $query->where('created_at', '<=', $this->filtros['fec_hasta_adm']);
        }
        if (!empty($this->filtros['nro_parte'])) {
            $query->where('id', $this->filtros['nro_parte']);
        }
        if (!empty($this->filtros['atencion'])) {
            $atencion = trim((string) $this->filtros['atencion']);
            $query->whereIn('id', function ($sub) use ($atencion) {
                $sub->select('id')
                    ->from('partes_cab')
                    ->where('atencion', 'like', '%' . $atencion . '%');
            });
        }

        $partes = $query->orderBy('created_at', 'asc')->paginate();

        return view('livewire.consumos.partes.consumo-partes-tabla', compact('partes'));
    }
}
```

---

## `resources/views/livewire/consumos/partes/consumo-partes-filtro.blade.php`

```blade
<form wire:submit="aplicar">
    <div class="row">
        <div class="form-group col-md-2">
            <label class="small mb-1" for="cobertura_id">Coberturas</label>
            <div class="choices-wide-dropdown" wire:ignore x-data="{
                    choices: null,
                    init() {
                        this.choices = initChoices(this.$refs.coberturaSelect, {
                            onChange: (value) => { $wire.set('cobertura_id', value) },
                        });
                    }
                }" x-on:filtro-limpiado.window="resetChoices(choices)">
                <select x-ref="coberturaSelect" class="form-select form-select-sm">
                    <option value="">-- Seleccione --</option>
                    @foreach ($coberturas as $item)
                        <option value="{{ $item->id }}" @selected($cobertura_id == $item->id)>{{ $item->sigla }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group col-md-2">
            <label class="small mb-1" for="centro_id">Centros</label>
            <select wire:model="centro_id" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
                @foreach ($centros as $item)
                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-md-2">
            <label class="small mb-1" for="profesional_id">Profesional</label>
            <select wire:model="profesional_id" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
                @foreach ($profesionales as $item)
                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2">
            <label class="small mb-1" for="estado_id">Estados</label>
            <div class="choices-nowrap" wire:ignore x-data="{
                    choices: null,
                    init() {
                        this.choices = initChoices(this.$refs.estadoSelect, {
                            removeItemButton: true,
                            onChange: (values) => { $wire.set('estado_id', values) },
                        });
                    }
                }" x-on:filtro-limpiado.window="resetChoices(choices, true)">
                <select x-ref="estadoSelect" class="form-select form-select-sm" multiple>
                    @foreach ($estados as $item)
                        <option value="{{ $item->id }}" @selected(in_array($item->id, $estado_id))>{{ $item->descripcion }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <label class="small mb-1" for="nro_parte">Nro parte</label>
            <input class="form-control form-control-sm" wire:model="nro_parte" type="number"
                min="0" step=1 max="999999999" placeholder="número" />
        </div>
        <div class="col-md-2">
            <label class="small mb-1" for="atencion">Nro atención</label>
            <input class="form-control form-control-sm" wire:model="atencion" id="atencion"
                type="text" inputmode="numeric" maxlength="10" pattern="[0-9]*"
                placeholder="Nro atención" />
        </div>
    </div>
    <div class="row pt-2">
        <div class="col-md-2">
            <label class="small mb-1" for="fec_desde">Fec. qx desde</label>
            <input class="form-control form-control-sm" wire:model="fec_desde" type="date"
                placeholder="Ingrese fecha desde" />
        </div>
        <div class="col-md-2">
            <label class="small mb-1" for="fec_hasta">Fec. qx hasta</label>
            <input class="form-control form-control-sm" wire:model="fec_hasta" type="date"
                placeholder="Ingrese fecha hasta" />
        </div>
        <div class="form-group col-md-2">
            <label class="small mb-1" for="nombre">Paciente</label>
            <input type="text" wire:model="nombre" class="form-control form-control-sm" placeholder="Nombre del paciente">
        </div>
        <div class="col-md-2">
            <label class="small mb-1" for="fec_desde_adm">Fec. carga desde</label>
            <input class="form-control form-control-sm" wire:model="fec_desde_adm" type="date"
                placeholder="Ingrese fecha desde" />
        </div>
        <div class="col-md-2">
            <label class="small mb-1" for="fec_hasta_adm">Fec. carga hasta</label>
            <input class="form-control form-control-sm" wire:model="fec_hasta_adm" type="date"
                placeholder="Ingrese fecha hasta" />
        </div>
        <div class="form-group col-md-2 d-flex align-items-end justify-content-end gap-1">
            <button class="btn btn-primary btn-sm w-100" type="submit" x-data
                x-init="new bootstrap.Tooltip($el)" data-bs-placement="top" data-bs-title="Aplicar el filtro ingresado"><i class="fa-solid fa-filter me-1"></i>Filtrar
                partes</button>
            <button class="btn btn-warning btn-sm" type="button" wire:click="limpiar" x-data
                x-init="new bootstrap.Tooltip($el)" data-bs-placement="top" data-bs-title="Limpiar filtro">
                <i class="fa-solid fa-eraser"></i>
            </button>
        </div>
    </div>
</form>
```

---

## `public/css/custom.css` (fragmento agregado)

Se insertó después del bloque `.choices-nowrap`:

```css
  /* ---------- Choices.js: desplegable ancho ----------
     Para selects (uno o varios) ubicados en columnas angostas: el listado
     se ensancha y cada opcion se muestra en una sola linea. */
  .choices-wide-dropdown .choices__list--dropdown,
  .choices-wide-dropdown .choices__list[aria-expanded] {
    width: max-content;
    min-width: 100%;
    max-width: 340px;
  }
  .choices-wide-dropdown .choices__list--dropdown .choices__item,
  .choices-wide-dropdown .choices__list[aria-expanded] .choices__item {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
```
