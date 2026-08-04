<?php

namespace App\Http\Controllers\cargas;

use App\Models\ParteDet;
use App\Http\Controllers\Controller;
use App\Services\ArchivoDescargaService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParteController extends Controller
{
    public function download(int $id, ArchivoDescargaService $archivoDescargaService): BinaryFileResponse
    {
        $parte = ParteDet::findOrFail($id);

        return $archivoDescargaService->descargar('partes', $parte->parte_cab_id . '/' . $parte->path);
    }
}
