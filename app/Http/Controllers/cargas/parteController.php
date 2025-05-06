<?php

namespace App\Http\Controllers\cargas;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Centro;
use App\Models\Estado;
use App\Models\Calendar;
use App\Models\Paciente;
use App\Models\Cobertura;
use App\Models\Documento;
use App\Models\Parte_cab;
use App\Models\Parte_det;
use App\Models\Profesional;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ParteController extends Controller
{
    public function filtrar(Request $request)
    {
        $coberturas = Cobertura::orderBy('nombre')->get();
        $centros = Centro::orderBy('nombre')->get();
        $profesionales = Profesional::get();
        $estados = Estado::get();
        $users = User::get();
    
        dd($request->all());
        session(['form_parte' => $request->all()]);

        // Capturar todos los parámetros del filtro
        $filtros = $request->only([
            'cobertura_id', 'centro_id', 'profesional_id', 'user_id', 
            'nombre', 'nro_parte', 'fec_desde', 'fec_hasta', 
            'estado_id', 'fec_desde_adm', 'fec_hasta_adm'
        ]);

        $query = Parte_cab::vParteCab();
    
        if (!empty($filtros['cobertura_id'])) {
            $query->where('cobertura_id', '=', $filtros['cobertura_id']);
        }
        if (!empty($filtros['centro_id'])) {
            $query->where('centro_id', '=', $filtros['centro_id']);
        }
        if (!empty($filtros['profesional_id'])) {
            $query->where('profesional_id', '=', $filtros['profesional_id']);
        }
        if (!empty($filtros['estado_id'])) {
            $query->where('estado_id', '=', $filtros['estado_id']);
        }
        if (!empty($filtros['user_id'])) {
            $query->where('user_id', '=', $filtros['user_id']);
        }
        if (!empty($filtros['nombre'])) {
            $query->where('paciente', 'like', '%' . $filtros['nombre'] . '%');
        }
        if (!empty($filtros['fec_desde'])) {
            $query->where('fec_prestacion_orig', '>=', $filtros['fec_desde']);
        }
        if (!empty($filtros['fec_hasta'])) {
            $query->where('fec_prestacion_orig', '<=', $filtros['fec_hasta']);
        }
        if (!empty($filtros['fec_desde_adm'])) {
            $query->where('created_at', '>=', $filtros['fec_desde_adm']);
        }
        if (!empty($filtros['fec_hasta_adm'])) {
            $query->whereDate('created_at', '<=', $filtros['fec_hasta_adm'] ? 
                Carbon::parse($filtros['fec_hasta_adm'])->endOfDay() : null);
        }
        if (!empty($filtros['nro_parte'])) {
            $query->where('id', '=', $filtros['nro_parte']);
        }
    
        $partes = $query->orderBy('created_at', 'asc')
            ->paginate(10)
            ->appends($filtros); // Usar los filtros extraídos
    
        return view('cargas.cab.parte', compact(
            'partes',
            'coberturas',
            'centros',
            'users',
            'estados',
            'profesionales',
            'filtros' // Pasar los filtros a la vista
        ));
    }
    
    public function create()
    {
        $gerenciadoras = Auth::user()->gerenciadoras;
        $centro_id = User::find(Auth()->user()->id)->centro_id;
        if ($centro_id) {
            $centros = Centro::where("id", $centro_id)->get();
        } else {
            $centros = Centro::orderby("nombre")->get();
        }
        
        $paciente = new Paciente();
        $coberturas = Cobertura::orderby("nombre")->get();
        $profesionales = Profesional::get();
        $parte = new Parte_cab();

        return view('cargas.cab.create', compact('parte', 'gerenciadoras', 'centros', 'paciente', 'coberturas', 'profesionales'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => 'required|string|max:20',
            'cobertura_id' => 'required',
            'centro_id' => 'required',
            'profesional_id' => 'required',
            'nombre' => 'required',
            'gerenciadora_id' => 'required',
            'fec_nacimiento' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $year = explode('-', $value)[0];
                    $currentYear = date('Y');
                    if ($year < 1900 || $year > $currentYear) {
                        $fail("El campo fecha de nacimiento debe ser un año entre 1900 y $currentYear.");
                    }
                },
            ],
        ]);

        $paciente = Paciente::where('dni', $request->dni)->first();
        if (empty($paciente)) {
            $paciente = new Paciente();
        }
        $paciente->dni = $request->dni;
        $paciente->nombre = $request->nombre;
        $paciente->fec_nacimiento = $request->fec_nacimiento;
        $paciente->save();
        $paciente_id = $paciente->id;

        if (empty($request->parte_id)) {
            $parte = new Parte_cab();
            $msg = "creado";
        } else {
            $parte = Parte_cab::where('id', $request->parte_id)->first();
            $msg = "actualizado";
        }
        $parte->paciente_id = $paciente_id;
        $parte->gerenciadora_id = $request->gerenciadora_id;
        $parte->centro_id = $request->centro_id;
        $parte->cobertura_id = $request->cobertura_id;
        $parte->profesional_id = $request->profesional_id;
        $parte->observaciones = $request->observaciones;
        $parte->fec_prestacion = $request->fec_prestacion;
        $parte->user_id = Auth()->user()->id;
        $parte->save();

        return back()->withInput()
            ->with(['success' => "Se ha $msg la cabecera del parte correctamente, nro: {$parte->id}.",
                    'ses_parte_id' => $parte->id]);
    }

    public function edit($id)
    {
        $gerenciadoras = Auth::user()->gerenciadoras;
        $parte = Parte_cab::find($id);
        $paciente = Paciente::where('id', $parte->paciente_id)->first();
        $centros = Centro::orderby("nombre")->get();
        $coberturas = Cobertura::orderby("nombre")->get();
        $profesionales = Profesional::get();
        session(['ses_parte_id' => $parte->id]);

        return view('cargas.cab.create', compact('parte', 'gerenciadoras', 'centros', 'paciente', 'coberturas', 'profesionales'));
    }

    public function destroy($id)
    {
        $parte = Parte_cab::find($id);
        //si esta ingresado o observado no se puede borrar
        if (in_array($parte->estado, [1,2])) {
            return redirect()->back()->with('error', "No es posible borrar el parte {$parte->id} con el estado actual.");
        }
        $parte->delete();

        return redirect()->route('partes_cab.filtrar')
        ->with('success', 'Parte borrado correctamente.');
    }

    public function createDet(int $id)
    {
        $documentos = Documento::where("tipo", "like", "%parte%")->get();
        $estados = estado::get();
        $partes = Parte_det::with('documento')->where('parte_cab_id', $id)->paginate(5);
        $parte = new Parte_det();
        $observaciones = Parte_cab::find($id)->observaciones;
        $parte_cab_id = $id;

        return view('cargas.det.form', compact('estados', 'parte', 'partes', 'documentos', 'parte_cab_id', 'observaciones'));
    }

    public function storeDet(Request $request)
    {
        $validatedData = $request->validate([
            'parte_cab_id' => 'required|exists:partes_cab,id',
            'documento_id' => 'required|exists:documentos,id',
            'archivo' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:5548',
            'nro_hoja' => 'required|integer'
        ]);

        $archivo = $request->file('archivo');
        $archivoNombre = $archivo->getClientOriginalName();

        // Guardar en el disco 'partes' configurado en filesystems.php
        $path = Storage::disk('partes')->putFileAs($request->parte_cab_id, $archivo, $archivoNombre);

        // Guardar el path en la base de datos
        $parteDet = new Parte_det();
        $parteDet->parte_cab_id = $request->parte_cab_id;
        $parteDet->documento_id = $request->documento_id;
        $parteDet->nro_hoja = $request->nro_hoja;
        $parteDet->path = $archivoNombre;
        $parteDet->save();

        return back();
    }

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

    public function destroyDet(int $id)
    {
        $parte_det = Parte_det::find($id);
        $parte = Parte_cab::where("id", $parte_det->parte_cab_id)->first();
        if (!in_array($parte->estado_id, [1,2])) {
            return redirect()->route('partes_det.create', $parte->id)
            ->with('error', 'No es posible borrar la documentación con el estado actual del parte.');
        }
        $parte_det->delete();

        return redirect()->route('partes_det.create', $parte->id)
        ->with('success', 'Detalle de Parte borrado correctamente.');
    }

    public function calendar()
    {
        $calendario = Calendar::all();
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
        
            $name = User::where('id', $item->user_id)->first()->name;
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
        if ($calendar && $calendar->user_id <> Auth()->user()->id) {
            return redirect()->route('partes_cab.calendar')
                ->withErrors(['error' => 'No es posible modificar el calendario de otro usuario.']);
        }

        if ($request->has('cancelar') && $request->cancelar == 'on')
        {
            $partes = Parte_cab::where('fecha_prestacion', $request->fecha)->exists();
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
        }
        
        $calendar->user_id = Auth()->user()->id;
        $calendar->fecha_ini = $request->fecha;
        $calendar->observaciones = $request->observaciones;
        $calendar->cerrado = $request->cerrado == 'on' ? 1 : 0  ;
        $calendar->save();
        
        return redirect()->route('partes_cab.calendar');
    }
}
