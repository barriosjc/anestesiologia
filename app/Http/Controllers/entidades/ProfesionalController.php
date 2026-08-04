<?php

namespace App\Http\Controllers\entidades;

use App\Models\ProfesionalDocumento;
use App\Services\ArchivoDescargaService;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfesionalController extends Controller
{
    public function downloadDocum(int $id, ArchivoDescargaService $archivoDescargaService): BinaryFileResponse
    {
        $prof_docum = ProfesionalDocumento::findOrFail($id);

        return $archivoDescargaService->descargar(
            'usuarios',
            $prof_docum->profesional_id . '/' . $prof_docum->path,
            true
        );
    }
}
