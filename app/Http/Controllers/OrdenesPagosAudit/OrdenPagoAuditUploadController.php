<?php

namespace App\Http\Controllers\OrdenesPagosAudit;

use App\Exceptions\LlamaParseException;
use App\Http\Controllers\Controller;
use App\Services\LlamaParse\LlamaParseService;
use App\Services\OrdenPagoAuditImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * Recibe los archivos del boton "Subir" de Ordenes de Pago - Auditoria.
 *
 * La subida la hace el navegador con un POST multipart normal (XHR) contra
 * esta ruta, sin pasar por la subida temporal de Livewire. De esa forma:
 *  - el archivo viaja en un unico request (no hay URL firmada intermedia),
 *  - se puede mostrar el progreso real de la transferencia,
 *  - cualquier error del servidor se devuelve como JSON y se muestra tal cual.
 */
class OrdenPagoAuditUploadController extends Controller
{
    /** Extensiones que se importan como planilla / CSV. */
    private const EXTENSIONES_IMPORTACION = ['csv', 'txt', 'xls', 'xlsx'];

    /**
     * Tamano maximo real que se puede subir: el menor entre el maximo de la
     * aplicacion y los limites de PHP (upload_max_filesize y post_max_size).
     */
    public static function maxMb(): int
    {
        $app = max(1, (int) config('llamaparse.max_file_mb', 100));

        return max(1, min(
            $app,
            self::phpMb(ini_get('upload_max_filesize')),
            self::phpMb(ini_get('post_max_size'))
        ));
    }

    /**
     * Convierte un valor de php.ini (ej: "16M", "1G", "512K") a MB.
     */
    private static function phpMb(string|false $valor): int
    {
        $valor = trim((string) $valor);

        if ($valor === '' || ! is_numeric(substr($valor, 0, -1))) {
            return 9999;
        }

        $numero = (int) $valor;

        return match (strtolower(substr($valor, -1))) {
            'g'     => $numero * 1024,
            'm'     => max(1, $numero),
            'k'     => max(1, (int) ceil($numero / 1024)),
            default => max(1, (int) ceil($numero / 1024 / 1024)),
        };
    }

    public function store(Request $request): JsonResponse
    {
        if ($rechazo = $this->validarRequest($request)) {
            return $rechazo;
        }

        /** @var UploadedFile $archivo */
        $archivo = $request->file('archivo');
        $extension = strtolower((string) $archivo->getClientOriginalExtension());

        if ($extension === 'pdf') {
            return $this->procesarPdf($archivo);
        }

        if (! in_array($extension, self::EXTENSIONES_IMPORTACION, true)) {
            return $this->errorValidacion('El archivo debe ser CSV, TXT, XLS, XLSX o PDF.');
        }

        return $this->importar($archivo, $extension);
    }

    /**
     * Valida que el archivo haya llegado completo y no supere el maximo de la app.
     */
    private function validarRequest(Request $request): ?JsonResponse
    {
        $archivo = $request->file('archivo');

        if (! $archivo instanceof UploadedFile || ! $archivo->isValid()) {
            // Cuando PHP rechaza el archivo (tamano, tmp dir, permisos) el
            // request llega sin el file valido. Se deja registro para poder
            // diagnosticarlo y se devuelve el limite real del servidor.
            Log::warning('OrdenPagoAudit: el archivo no llego al servidor.', [
                'files' => $_FILES,
                'content_length' => $request->server('CONTENT_LENGTH'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'upload_tmp_dir' => ini_get('upload_tmp_dir'),
            ]);

            return $this->errorValidacion(
                'El archivo no llegó al servidor. El límite de PHP es '
                .ini_get('upload_max_filesize').' por archivo y '
                .ini_get('post_max_size').' por request.'
            );
        }

        $maxMb = self::maxMb();
        $maxKb = $maxMb * 1024;

        $validator = Validator::make(
            ['archivo' => $archivo],
            ['archivo' => ['required', 'file', 'max:'.$maxKb]],
            [
                'archivo.required' => 'Seleccioná un archivo.',
                'archivo.file'     => 'El archivo no llegó completo al servidor.',
                'archivo.uploaded' => 'El archivo no se pudo subir al servidor.',
                'archivo.max'      => 'El archivo supera el máximo permitido de '.$maxMb.' MB.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('archivo'),
                'errors'  => $validator->errors(),
            ], 422);
        }

        return null;
    }

    /**
     * Importa un CSV / XLSX / XLS de ordenes de pago.
     */
    private function importar(UploadedFile $archivo, string $extension): JsonResponse
    {
        try {
            // La ruta temporal de PHP no conserva la extension, por eso se le
            // pasa explicitamente para que el servicio elija el lector correcto.
            $resultado = app(OrdenPagoAuditImportService::class)
                ->importar($archivo->getRealPath(), $extension);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok'      => false,
                'message' => 'No se pudo leer el archivo: '.$e->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok'      => $resultado['success'],
            'message' => $resultado['message'],
            'errors'  => $resultado['errors'],
            'avisos'  => $resultado['errors'],
            'refresh' => $resultado['success'],
        ], $resultado['success'] ? 200 : 422);
    }

    /**
     * Guarda el PDF y lo envia a LlamaParse.
     */
    private function procesarPdf(UploadedFile $archivo): JsonResponse
    {
        try {
            $service = app(LlamaParseService::class);
            $documento = $service->registrar($archivo, auth()->id());
        } catch (LlamaParseException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok'      => false,
                'message' => 'No se pudo guardar el PDF: '.$e->getMessage(),
            ], 500);
        }

        $redirect = route('documentos_llamaparse.ver', $documento);

        try {
            $service->iniciar($documento);
        } catch (Throwable $e) {
            report($e);

            // El PDF ya quedo guardado y el documento en estado de error:
            // se abre el detalle para poder reprocesarlo desde ahi.
            return response()->json([
                'ok'         => true,
                'advertencia' => true,
                'message'    => 'El PDF se guardó, pero LlamaParse no lo recibió: '.$e->getMessage(),
                'redirect'   => $redirect,
                'refresh'    => true,
            ]);
        }

        return response()->json([
            'ok'       => true,
            'message'  => 'PDF enviado a LlamaParse.',
            'redirect' => $redirect,
            'refresh'  => true,
        ]);
    }

    private function errorValidacion(string $mensaje): JsonResponse
    {
        return response()->json([
            'message' => $mensaje,
            'errors'  => ['archivo' => [$mensaje]],
        ], 422);
    }
}
