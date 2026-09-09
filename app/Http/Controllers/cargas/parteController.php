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
<<<<<<< HEAD
        $parte = Parte_det::findOrFail($id);
=======
        $parte = ParteDet::findOrFail($id);
>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856

        return $archivoDescargaService->descargar('partes', $parte->parte_cab_id . '/' . $parte->path);
    }
}
