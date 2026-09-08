<?php

namespace App\Livewire\Reportes;

use App\Models\Centro;
use App\Models\Cobertura;
use App\Models\Estado;
use App\Models\Listado;
use App\Models\Periodo;
use App\Models\Profesional;
use App\Models\User;
use App\Services\Reports\ReportGeneratorService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class ReportesForm extends Component
{
    public ?int $reporte_id = null;
    public ?int $cobertura_id = null;
    public ?int $centro_id = null;
    public ?int $profesional_id = null;
    public ?string $nombre = null;
    public array $estados = [];
    public ?int $periodo_gen = null;
    public ?string $fec_desde = null;
    public ?string $fec_hasta = null;
    public ?string $estado_presupuesto = null;
    public ?int $user_id = null;
    public ?string $fec_desde_adm = null;
    public ?string $fec_hasta_adm = null;

    protected function filtroKeys(): array
    {
        return [
            'reporte_id', 'cobertura_id', 'centro_id', 'profesional_id', 'nombre',
            'estados', 'periodo_gen', 'fec_desde', 'fec_hasta', 'estado_presupuesto',
            'user_id', 'fec_desde_adm', 'fec_hasta_adm',
        ];
    }

    public function generar(ReportGeneratorService $reportGeneratorService): void
    {
        $filtros = [];
        foreach ($this->filtroKeys() as $key) {
            $filtros[$key] = $this->$key;
        }

        try {
            $reportGeneratorService->validar($this->reporte_id ? (int) $this->reporte_id : null, $filtros);
        } catch (\Exception $e) {
            $this->addError('reporte_id', $e->getMessage());
            return;
        }

        $filtros = array_filter($filtros, function ($valor) {
            return !is_null($valor) && $valor !== '' && $valor !== [];
        });

        $this->dispatch('reporte-pdf-listo', url: route('reportes.stream', $filtros));
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();

        return view('livewire.reportes.reportes-form', [
            'coberturas' => Cobertura::orderBy('nombre')->get(),
            'centros' => Centro::orderBy('nombre')->get(),
            'profesionales' => Profesional::orderBy('nombre')->get(),
            'listaEstados' => Estado::get(),
            'periodos' => Periodo::orderBy('nombre')->get(),
            'users' => User::get(),
            'listados' => Listado::when(!$user->hasRole('super-admin'), function ($query) use ($user) {
                $query->whereIn('role_id', $user->roles->pluck('id'));
            })->get(),
            'estadosPresupuesto' => [
                'I' => 'Ingresado',
                'P' => 'Pagado',
                'C' => 'Cancelado',
                'O' => 'Cobrado',
                'F' => 'Facturado',
            ],
        ]);
    }
}
