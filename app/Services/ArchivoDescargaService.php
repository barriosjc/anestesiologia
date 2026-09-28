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
        $nombre ??= basename($rutaRelativa);

        return $descargar
            ? response()->download($rutaAbsoluta, $nombre)
            : response()->file($rutaAbsoluta, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $nombre . '"',
            ]);
    }
}
