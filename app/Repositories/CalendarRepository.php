<?php

namespace App\Repositories;

use App\Models\Calendar;
use App\Models\Parte_cab;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CalendarRepository
{
    private const COLORES = [
        ['color' => '#ff0000', 'forecolor' => '#ffffff'],
        ['color' => '#00ff00', 'forecolor' => '#000000'],
        ['color' => '#0000ff', 'forecolor' => '#ffffff'],
        ['color' => '#ffff00', 'forecolor' => '#000000'],
        ['color' => '#ff00ff', 'forecolor' => '#ffffff'],
        ['color' => '#00ffff', 'forecolor' => '#000000'],
        ['color' => '#bbbb55', 'forecolor' => '#000000'],
    ];

    public function eventos(): array
    {
        $calendario = Calendar::all();
        $usuariosAdministrativos = User::role('administrativo')->orderBy('id')->get();

        $eventos = [];
        foreach ($calendario as $item) {
            $name = User::find($item->user_id)?->name ?? 'Sin nombre';
            $color = '';
            $textColor = '';
            foreach ($usuariosAdministrativos as $key => $user) {
                if ($user->id == $item->user_id) {
                    $name = $user->name;
                    $color = self::COLORES[$key % count(self::COLORES)]['color'];
                    $textColor = self::COLORES[$key % count(self::COLORES)]['forecolor'];
                    break;
                }
            }

            $eventos[] = [
                'title' => $name,
                'start' => $item->fecha_ini,
                'allDay' => true,
                'color' => $color,
                'textColor' => $textColor,
            ];

            if ($item->observaciones) {
                $eventos[] = [
                    'title' => $item->observaciones,
                    'start' => $item->fecha_ini,
                    'allDay' => true,
                    'color' => '#ffffff',
                    'textColor' => 'black',
                ];
            }

            if ($item->cerrado) {
                $eventos[] = [
                    'title' => '** CERRADO **',
                    'start' => $item->fecha_ini,
                    'end' => $item->fecha_ini,
                    'allDay' => true,
                    'color' => '#e481a9',
                    'textColor' => 'white',
                ];
            }
        }

        return $eventos;
    }

    public function guardar(array $datos): array
    {
        $userId = Auth::id();
        $calendar = Calendar::where('fecha_ini', $datos['fecha'])->first();

        if ($calendar && $calendar->user_id != $userId && !Auth::user()->hasRole('super-admin')) {
            return ['success' => false, 'mensaje' => 'No es posible modificar el calendario de otro usuario.'];
        }

        if (!empty($datos['cancelar'])) {
            if (Parte_cab::where('fec_prestacion', $datos['fecha'])->exists()) {
                return ['success' => false, 'mensaje' => 'No es posible cancelar la fecha porque tiene parte(s) cargada(s).'];
            }
            $calendar?->delete();

            return ['success' => true, 'mensaje' => 'Fecha cancelada ' . $datos['fecha'] . ' correctamente.'];
        }

        if (!$calendar) {
            $calendar = new Calendar();
            $calendar->user_id = $userId;
        }

        $calendar->fecha_ini = $datos['fecha'];
        $calendar->observaciones = $datos['observaciones'] ?? null;
        $calendar->cerrado = !empty($datos['cerrado']);
        $calendar->save();

        return ['success' => true, 'mensaje' => 'Fecha guardada correctamente.'];
    }
}
