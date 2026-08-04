<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ArchivoDescargaService
{
    public function descargar(string $disco, string $rutaRelativa, bool $descargar = false, ?string $nombre = null): Response
    {
        if (!Storage::disk($disco)->exists($rutaRelativa)) {
            abort(404, 'El archivo no existe.');
        }

        $rutaAbsoluta = Storage::disk($disco)->path($rutaRelativa);

        return $descargar
            ? response()->download($rutaAbsoluta, $nombre ?? basename($rutaRelativa))
            : response()->file($rutaAbsoluta);
    }
}
