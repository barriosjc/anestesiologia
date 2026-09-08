<?php

namespace App\Http\Controllers\cargas;

use App\Models\Parte_det;
use App\Http\Controllers\Controller;
use App\Services\ArchivoDescargaService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParteController extends Controller
{
    public function download(int $id, ArchivoDescargaService $archivoDescargaService): BinaryFileResponse
    {
        $parte = Parte_det::findOrFail($id);

        return $archivoDescargaService->descargar('partes', $parte->parte_cab_id . '/' . $parte->path);
    }
}
