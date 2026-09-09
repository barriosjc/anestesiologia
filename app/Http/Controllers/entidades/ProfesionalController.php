<?php

namespace App\Http\Controllers\entidades;

<<<<<<< HEAD
use App\Models\Profesional_documento;
=======
use App\Models\ProfesionalDocumento;
>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856
use App\Services\ArchivoDescargaService;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfesionalController extends Controller
{
    public function downloadDocum(int $id, ArchivoDescargaService $archivoDescargaService): BinaryFileResponse
    {
<<<<<<< HEAD
        $prof_docum = Profesional_documento::findOrFail($id);
=======
        $prof_docum = ProfesionalDocumento::findOrFail($id);
>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856

        return $archivoDescargaService->descargar(
            'usuarios',
            $prof_docum->profesional_id . '/' . $prof_docum->path,
            true
        );
    }
}
