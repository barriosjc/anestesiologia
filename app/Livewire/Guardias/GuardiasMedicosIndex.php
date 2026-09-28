<?php

namespace App\Livewire\Guardias;

use App\Repositories\FeriadoRepository;
use App\Repositories\GuardiaMedicoRepository;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class GuardiasMedicosIndex extends Component
{
    public int $anio;

    public int $mes;

    public ?int $profesionalId = null;

    public ?string $nombreFeriado = null;

    public ?string $feriadoFecha = null;

    public ?string $feriadoNombre = null;

    public bool $feriadoConfirmado = false;

    public array $asignadosPendientes = [];

    public array $marcados = [];

    public array $desasignados = [];

    public bool $incluirFeriados = false;

    public function mount(): void
    {
        $hoy = now();
        $this->anio = (int) $hoy->format('Y');
        $this->mes = (int) $hoy->format('n');
    }

    public function cambiarMes(int $delta): void
    {
        $fecha = Carbon::create($this->anio, $this->mes, 1)->addMonths($delta);
        $this->anio = (int) $fecha->format('Y');
        $this->mes = (int) $fecha->format('n');
        $this->limpiarEstadoLocal();
    }

    public function irAlMesActual(): void
    {
        $hoy = now();
        $this->anio = (int) $hoy->format('Y');
        $this->mes = (int) $hoy->format('n');
        $this->limpiarEstadoLocal();
    }

    private function limpiarEstadoLocal(): void
    {
        $this->marcados = [];
        $this->asignadosPendientes = [];
        $this->desasignados = [];
        $this->feriadoFecha = null;
        $this->feriadoNombre = null;
        $this->feriadoConfirmado = false;
    }

    public function marcaDia(string $fecha, FeriadoRepository $feriadoRepo): void
    {
        if ($this->feriadoFecha === $fecha) {
            $this->descartarFeriado(false);

            return;
        }

        if (isset($this->marcados[$fecha])) {
            unset($this->marcados[$fecha], $this->asignadosPendientes[$fecha]);
            $this->desasignados[$fecha] = true;

            return;
        }

        $nombre = trim((string) $this->nombreFeriado);

        if ($nombre === '') {
            $this->marcados[$fecha] = true;
            unset($this->desasignados[$fecha]);

            return;
        }

        if ($this->feriadoFecha !== null) {
            $this->dispatch(
                'aviso-feriado',
                mensaje: 'Solo se puede marcar 1 celda para crear un feriado. Desmarcá la celda del ' . $this->fechaLegible($this->feriadoFecha) . ' o limpiá el nombre del feriado.',
            );

            return;
        }

        $this->marcados[$fecha] = true;
        unset($this->desasignados[$fecha]);
        $this->feriadoFecha = $fecha;
        $this->feriadoNombre = $nombre;
        $this->feriadoConfirmado = false;

        $this->dispatch(
            'confirmar-feriado',
            fecha: $fecha,
            nombre: $nombre,
            fechaLegible: $this->fechaLegible($fecha),
            yaExiste: $feriadoRepo->tiene($fecha),
        );
    }

    public function confirmarFeriado(string $fecha, FeriadoRepository $feriadoRepo): void
    {
        if ($this->feriadoFecha !== $fecha) {
            return;
        }

        $nombre = trim((string) $this->feriadoNombre);

        if ($nombre === '') {
            $this->descartarFeriado();
            session()->flash('warning', 'Ingrese el nombre del feriado antes de marcar el día.');

            return;
        }

        if ($feriadoRepo->tiene($fecha)) {
            $this->feriadoConfirmado = true;
            session()->flash('info', 'El ' . $this->fechaLegible($fecha) . ' ya estaba cargado como feriado.');

            return;
        }

        $feriadoRepo->crear($fecha, $nombre);
        $this->feriadoConfirmado = true;

        session()->flash('success', 'Feriado "' . $nombre . '" cargado el ' . $this->fechaLegible($fecha) . '.');
    }

    public function descartarFeriado(bool $limpiarNombre = true): void
    {
        if ($this->feriadoFecha !== null) {
            $fecha = $this->feriadoFecha;
            unset($this->marcados[$fecha], $this->asignadosPendientes[$fecha]);
            $this->desasignados = array_diff_key($this->desasignados, [$fecha => true]);
        }

        $this->feriadoFecha = null;
        $this->feriadoNombre = null;
        $this->feriadoConfirmado = false;

        if ($limpiarNombre) {
            $this->nombreFeriado = null;
        }
    }

    public function asignar(): void
    {
        if (empty($this->marcados)) {
            session()->flash('info', 'No hay días marcados en este mes.');

            return;
        }

        if (!$this->profesionalId) {
            session()->flash('warning', 'Seleccione un médico para asignar.');

            return;
        }

        $total = count($this->marcados);

        foreach (array_keys($this->marcados) as $fecha) {
            $this->asignadosPendientes[$fecha] = (int) $this->profesionalId;
            unset($this->marcados[$fecha], $this->desasignados[$fecha]);
        }

        session()->flash('success', 'Médico asignado a ' . $total . ' día(s). Presioná Confirmar para guardar.');
    }

    public function confirmar(FeriadoRepository $feriadoRepo, GuardiaMedicoRepository $guardiaRepo): void
    {
        if (empty($this->asignadosPendientes) && empty($this->desasignados)) {
            session()->flash('info', 'No hay cambios para confirmar.');

            return;
        }

        $total = $guardiaRepo->confirmar($this->asignadosPendientes, $feriadoRepo);

        foreach (array_keys($this->desasignados) as $fecha) {
            $guardiaRepo->quitarDia($fecha);
        }

        $desasignados = count($this->desasignados);
        $this->asignadosPendientes = [];
        $this->marcados = [];
        $this->desasignados = [];

        $mensaje = "Guardias confirmadas: {$total} día(s) asignado(s)";
        if ($desasignados > 0) {
            $mensaje .= ", {$desasignados} día(s) desasignado(s)";
        }
        $mensaje .= '.';

        session()->flash('success', $mensaje);
    }

    public function render(FeriadoRepository $feriadoRepo, GuardiaMedicoRepository $guardiaRepo): \Illuminate\Contracts\View\View
    {
        $primerDia = Carbon::create($this->anio, $this->mes, 1);
        $offset = ($primerDia->dayOfWeek + 6) % 7;
        $totalCeldas = (int) ceil(($offset + $primerDia->daysInMonth) / 7) * 7;

        $toma = $guardiaRepo->mes($this->anio, $this->mes);
        $feriados = $feriadoRepo->mes($this->anio, $this->mes);
        $medicos = $guardiaRepo->medicos();
        $nombresMedicos = $medicos->pluck('nombre', 'id');
        $indiceId = $medicos->pluck('id')->flip()->all();
        $hoyKey = now()->format('Y-m-d');

        $celdas = [];
        for ($i = 0; $i < $totalCeldas; $i++) {
            $fecha = $primerDia->copy()->subDays($offset)->addDays($i);
            $key = $fecha->format('Y-m-d');
            $row = $toma->get($key);
            $feriado = $feriados->get($key);
            $pendienteId = $this->asignadosPendientes[$key] ?? null;

            $color = null;
            $medicoNombre = null;
            $marcado = isset($this->marcados[$key]);
            $desasignado = isset($this->desasignados[$key]);

            if ($marcado || $desasignado) {
                $pendienteId = null;
                $row = null;
            } else {
                $pendienteId = $this->asignadosPendientes[$key] ?? null;
            }

            if ($pendienteId) {
                $color = $guardiaRepo->colorPara($pendienteId, $indiceId[$pendienteId] ?? $pendienteId);
                $medicoNombre = $nombresMedicos->get($pendienteId);
            } elseif ($row?->color) {
                $color = $row->color;
                $medicoNombre = $row->medico?->nombre;
            }

            $celdas[] = [
                'fecha' => $key,
                'dia' => (int) $fecha->format('j'),
                'enMes' => $fecha->month === $this->mes && $fecha->year === $this->anio,
                'esHoy' => $key === $hoyKey,
                'esSabado' => $fecha->isSaturday(),
                'esDomingo' => $fecha->isSunday(),
                'medicoNombre' => $medicoNombre,
                'color' => $color,
                'letraColor' => $color ? $this->textoDeColor($color) : '#333',
                'esFeriado' => $feriado !== null,
                'feriadoNombre' => $feriado?->nombre,
                'esFeriadoPendiente' => $this->feriadoFecha === $key,
                'feriadoConfirmado' => $this->feriadoConfirmado,
                'marcado' => $marcado,
                'desasignado' => $desasignado,
            ];
        }

        $semanas = array_chunk($celdas, 7);

        return view('livewire.guardias.guardias-medicos-index', [
            'medicos' => $medicos,
            'semanas' => $semanas,
            'tituloMes' => $primerDia->translatedFormat('F Y'),
            'pintados' => count($feriados),
            'pendientes' => count($this->asignadosPendientes),
            'feriadoFechaLegible' => $this->feriadoFecha ? $this->fechaLegible($this->feriadoFecha) : null,
        ]);
    }

    private function fechaLegible(string $fecha): string
    {
        return Carbon::parse($fecha)->translatedFormat('d/m/Y');
    }

    private function textoDeColor(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $luminancia = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminancia > 0.6 ? '#333' : '#fff';
    }
}