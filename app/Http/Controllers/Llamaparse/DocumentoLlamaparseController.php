<?php

namespace App\Http\Controllers\Llamaparse;

use App\Http\Controllers\Controller;
use App\Models\DocumentoLlamaparse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentoLlamaparseController extends Controller
{
    public function archivo(DocumentoLlamaparse $documento): BinaryFileResponse
    {
        $disk = (string) config('llamaparse.disk', 'local');

        abort_unless(Storage::disk($disk)->exists($documento->archivo), 404);

        return response()->file(
            Storage::disk($disk)->path($documento->archivo),
            [
                'Content-Type' => $documento->mime,
                'Content-Disposition' => 'inline; filename="documento-'.$documento->id.'.pdf"',
            ]
        );
    }

    public function xlsx(DocumentoLlamaparse $documento): BinaryFileResponse
    {
        $disk = (string) config('llamaparse.disk', 'local');

        abort_unless($documento->xlsx_archivo && Storage::disk($disk)->exists($documento->xlsx_archivo), 404);

        return response()->download(
            Storage::disk($disk)->path($documento->xlsx_archivo),
            'llamaparse-'.$documento->id.'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
