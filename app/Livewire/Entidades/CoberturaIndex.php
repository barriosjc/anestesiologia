<?php

namespace App\Livewire\Entidades;

use App\Models\Cobertura;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class CoberturaIndex extends Component
{
    public ?string $text = null;

    public ?int $modalId = null;
    public ?string $nombre = null;
    public ?string $sigla = null;
    public ?string $cuit = null;
    public ?string $cod_uom = null;
    public ?int $edad_desde = null;
    public ?int $edad_hasta = null;
    public ?int $porcentaje_adic = null;

    public function mount(): void
    {
        $this->text = request()->filled('text') ? (string) request('text') : null;
    }

    public function abrirModalNuevo(): void
    {
        $this->reset(['modalId', 'nombre', 'sigla', 'cuit', 'cod_uom', 'edad_desde', 'edad_hasta', 'porcentaje_adic']);
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'coberturaModal');
    }

    public function abrirModalEditar(int $id): void
    {
        $cobertura = Cobertura::findOrFail($id);

        $this->modalId = $cobertura->id;
        $this->nombre = $cobertura->nombre;
        $this->sigla = $cobertura->sigla;
        $this->cuit = $cobertura->cuit;
        $this->cod_uom = $cobertura->cod_uom;
        $this->edad_desde = $cobertura->edad_desde;
        $this->edad_hasta = $cobertura->edad_hasta;
        $this->porcentaje_adic = $cobertura->porcentaje_adic;
        $this->resetValidation();
        $this->dispatch('open-modal', modal: 'coberturaModal');
    }

    public function guardar(): void
    {
        $this->cod_uom = $this->normalizarCodUom($this->cod_uom);

        $this->validate([
            'nombre'          => ['required', 'string', 'max:200'],
            'cuit'            => ['required', 'string', 'max:15'],
            'cod_uom'         => ['nullable', 'string', 'max:100', 'regex:/^[0-9]+(\s*,\s*[0-9]+)*$/'],
            'sigla'           => ['required', 'string', 'max:45'],
            'edad_desde'      => ['nullable', 'integer'],
            'edad_hasta'      => ['nullable', 'integer'],
            'porcentaje_adic' => ['nullable', 'integer'],
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.max'           => 'El nombre no puede superar los 200 caracteres.',
            'cuit.required'        => 'El CUIT es obligatorio.',
            'cuit.max'             => 'El CUIT no puede superar los 15 caracteres.',
            'cod_uom.max'          => 'El código UOM no puede superar los 100 caracteres.',
            'cod_uom.regex'        => 'Ingresá uno o más códigos UOM numéricos separados por coma (ej: 84, 274).',
            'sigla.required'       => 'La sigla es obligatoria.',
            'sigla.max'            => 'La sigla no puede superar los 45 caracteres.',
            'edad_desde.integer'   => 'La edad desde debe ser un número entero.',
            'edad_hasta.integer'   => 'La edad hasta debe ser un número entero.',
            'porcentaje_adic.integer' => 'El porcentaje adicional debe ser un número entero.',
        ]);

        $data = [
            'nombre'          => $this->nombre,
            'cuit'            => $this->cuit,
            'cod_uom'         => $this->cod_uom,
            'sigla'           => $this->sigla,
            'edad_desde'      => $this->edad_desde,
            'edad_hasta'      => $this->edad_hasta,
            'porcentaje_adic' => $this->porcentaje_adic,
        ];

        if ($this->modalId) {
            Cobertura::findOrFail($this->modalId)->update($data);
        } else {
            Cobertura::create($data);
        }

        $this->dispatch('close-modal', modal: 'coberturaModal');
        $this->reset(['modalId', 'nombre', 'sigla', 'cuit', 'cod_uom', 'edad_desde', 'edad_hasta', 'porcentaje_adic']);
        session()->flash('success', 'Cobertura guardada correctamente.');
    }

    public function borrar(int $id): void
    {
        Cobertura::findOrFail($id)->delete();
        session()->flash('success', 'Cobertura borrada correctamente.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $busqueda = $this->normalizar($this->text);

        $coberturas = Cobertura::orderBy('nombre')
            ->get()
            ->filter(fn (Cobertura $cobertura) => $this->coincide($cobertura, $busqueda))
            ->values();

        return view('livewire.entidades.cobertura-index', compact('coberturas'));
    }

    /**
     * Normaliza el cod_uom: saca espacios, deja los códigos separados por
     * coma y descarta las partes vacías. Ej: "84, 274" -> "84,274".
     */
    private function normalizarCodUom(?string $codUom): ?string
    {
        if ($codUom === null || trim($codUom) === '') {
            return null;
        }

        $partes = array_values(array_filter(
            array_map(fn (string $parte) => trim($parte), explode(',', $codUom)),
            fn (string $parte) => $parte !== ''
        ));

        return $partes === [] ? null : implode(',', $partes);
    }

    /**
     * Indica si la cobertura coincide con la busqueda. Compara por nombre,
     * sigla, CUIT y cod_uom, ignorando mayusculas y acentos.
     */
    private function coincide(Cobertura $cobertura, string $busqueda): bool
    {
        if ($busqueda === '') {
            return true;
        }

        foreach (['nombre', 'sigla', 'cod_uom'] as $campo) {
            $valor = $this->normalizar((string) $cobertura->$campo);

            if ($valor !== '' && str_contains($valor, $busqueda)) {
                return true;
            }
        }

        // El CUIT se compara solo con los digitos, para poder buscarlo con o
        // sin guiones.
        $cuit = $this->soloDigitos((string) $cobertura->cuit);
        $busquedaCuit = $this->soloDigitos($busqueda);

        if ($cuit !== null
            && $cuit !== ''
            && $busquedaCuit !== null
            && $busquedaCuit !== ''
            && str_contains($cuit, $busquedaCuit)) {
            return true;
        }

        return false;
    }

    /**
     * Normaliza un texto para la busqueda: mayusculas, sin acentos y sin
     * espacios repetidos, para que "medico" encuentre "Médico".
     */
    private function normalizar(?string $texto): string
    {
        if ($texto === null) {
            return '';
        }

        $texto = mb_strtoupper(trim($texto));
        $texto = strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'Ü' => 'U', 'Ñ' => 'N', 'Ç' => 'C',
        ]);

        return trim((string) preg_replace('/\s+/', ' ', $texto));
    }

    /**
     * Deja solo los digitos de un texto (quita guiones, puntos y espacios).
     */
    private function soloDigitos(?string $texto): ?string
    {
        if ($texto === null) {
            return null;
        }

        $digitos = (string) preg_replace('/\D+/', '', $texto);

        return $digitos === '' ? null : $digitos;
    }
}
