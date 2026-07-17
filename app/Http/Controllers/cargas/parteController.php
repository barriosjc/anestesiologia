<?php

namespace App\Http\Controllers\cargas;

use App\Models\Parte_det;
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
}
