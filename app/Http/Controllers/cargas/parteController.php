<?php

namespace App\Http\Controllers\cargas;

use App\Models\User;
use App\Models\Calendar;
use App\Models\Parte_cab;
use App\Models\Parte_det;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ParteController extends Controller
{
    public function download($id)
    {
        $parte = Parte_det::find($id);
        $rutaArchivo = storage_path('app/public/partes/') . $parte->parte_cab_id . "/" . $parte->path;
        if (!Storage::disk('partes')->exists($parte->parte_cab_id . "/" . $parte->path)) {
            abort(404, 'El archivo no existe.');
        }

        // Obtener el nombre original del archivo
        $nombreOriginal = basename($rutaArchivo);

        // Retornar una respuesta de descarga
        // return response()->download($rutaArchivo, $nombreOriginal);
        return response()->file($rutaArchivo);
    }

    public function calendar()
    {
        $calendario = Calendar::all();
        $color = #deb728ff;
        $textColor = #000000;
        $calendar = [];
        foreach ($calendario as $item) {
            $name = "";
            $color = "";
            $colores = [
                ['color' => '#ff0000', 'forecolor' => '#ffffff'],
                ['color' => '#00ff00', 'forecolor' => '#000000'],
                ['color' => '#0000ff', 'forecolor' => '#ffffff'],
                ['color' => '#ffff00', 'forecolor' => '#000000'],
                ['color' => '#ff00ff', 'forecolor' => '#ffffff'],
                ['color' => '#00ffff', 'forecolor' => '#000000'],
                ['color' => '#bbbb55', 'forecolor' => '#000000'],
            ];
        
            // $name = User::where('id', $item->user_id)->first()->name;
            $name = User::find($item->user_id)?->name ?? 'Sin nombre';
            $users = User::role('administrativo')->orderby('id')->get();
            foreach ($users as $key => $user) {
                if ($user->id == $item->user_id) {
                    $name = $user->name;
                    $color = $colores[$key]['color'];
                    $textColor = $colores[$key]['forecolor'];
                    break;
                }
            }
        
            $calendar[] = [
                'title' => $name,
                'start' => $item->fecha_ini,
                'allDay' => true,
                'color' => $color,
                'textColor' => $textColor,
            ];
        
            if ($item->observaciones) {
                $calendar[] = [
                    'title' => $item->observaciones,
                    'start' => $item->fecha_ini,
                    'allDay' => true,
                    'color' => '#ffffff',
                    'textColor' => 'black',
                ];
            }
            // Añadimos el evento "CERRADO" al final.
            if ($item->cerrado) {
                $calendar[] = [
                    'title' => "** CERRADO **",
                    'start' => $item->fecha_ini,
                    'end' => $item->fecha_ini,
                    'allDay' => true,
                    'color' => '#e481a9',
                    'textColor' => 'white',
                ];
            }
        }
        
        return view("cargas.calendar.index", compact('calendar'));
    }

    public function calendarGuardar(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:255',
            'cerrado' => 'nullable|string',
            'cancelar' => 'nullable|string',
            ]);

        $calendar = Calendar::where('fecha_ini', $request->fecha)->first();
        if ($calendar && $calendar->user_id <> Auth()->user()->id && ! Auth()->user()->hasRole('super-admin')) {
            return redirect()->route('partes_cab.calendar')
                ->withErrors(['error' => 'No es posible modificar el calendario de otro usuario.']);
        }

        if ($request->has('cancelar') && $request->cancelar == 'on')
        {
            $partes = Parte_cab::where('fec_prestacion', $request->fecha)->exists();
            if ($partes) {
                return redirect()->route('partes_cab.calendar')
                    ->withErrors(['error' => 'No es posible cancelar la fecha porque tiene parte(s) cargada(s).']);
            }   
            $calendar->delete();
            return redirect()->route('partes_cab.calendar')
                ->with('success', 'Fecha cancelada '. $request->fecha .' correctamente');
        }

        if (!$calendar) {
            $calendar = new Calendar();
            $calendar->user_id = Auth()->user()->id;
        }
        
        $calendar->fecha_ini = $request->fecha;
        $calendar->observaciones = $request->observaciones;
        $calendar->cerrado = $request->cerrado == 'on' ? 1 : 0  ;
        $calendar->save();
        
        return redirect()->route('partes_cab.calendar');
    }
}
