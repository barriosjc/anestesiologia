<?php

namespace App\Services\LlamaParse;

use App\Enums\EstadoDocumentoLlamaparse;
use App\Exceptions\LlamaParseException;
use App\Models\DocumentoLlamaparse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class LlamaParseService
{
    public function __construct(private readonly XlsxJsonService $xlsx)
    {
    }

    public function configurado(): bool
    {
        return trim((string) config('llamaparse.api_key')) !== '';
    }

    public function registrar(UploadedFile $archivo, ?int $userId = null): DocumentoLlamaparse
    {
        if (! $this->configurado()) {
            throw new LlamaParseException('Falta configurar LLAMAPARSE_API_KEY en el servidor.');
        }

        $mime = (string) $archivo->getMimeType();

        if ($mime !== 'application/pdf') {
            throw new LlamaParseException('El archivo debe ser un PDF.');
        }

        $uuid = (string) Str::uuid();
        $disk = (string) config('llamaparse.disk', 'local');
        $base = trim((string) config('llamaparse.ruta', 'llamaparse'), '/');
        $ruta = Storage::disk($disk)->putFileAs($base.'/'.$uuid, $archivo, 'documento.pdf');

        if (! is_string($ruta)) {
            throw new LlamaParseException('No se pudo guardar el PDF. Intenta nuevamente.');
        }

        return DocumentoLlamaparse::create([
            'uuid' => $uuid,
            'user_id' => $userId,
            'archivo' => $ruta,
            'nombre_original' => $archivo->getClientOriginalName(),
            'mime' => $mime,
            'tamano' => (int) $archivo->getSize(),
            'estado' => EstadoDocumentoLlamaparse::Pendiente,
            'tier' => (string) config('llamaparse.tier', 'agentic'),
            'progreso' => 0,
            'progreso_mensaje' => 'Pendiente',
        ]);
    }

    public function iniciar(DocumentoLlamaparse $documento): void
    {
        if (! $this->configurado()) {
            throw new LlamaParseException('Falta configurar LLAMAPARSE_API_KEY en el servidor.');
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $documento->update([
            'estado' => EstadoDocumentoLlamaparse::Procesando,
            'progreso' => 5,
            'progreso_mensaje' => 'Subiendo PDF a LlamaParse',
            'error' => null,
        ]);

        try {
            $disk = (string) config('llamaparse.disk', 'local');
            $ruta = Storage::disk($disk)->path($documento->archivo);
            $contenido = file_get_contents($ruta);

            if ($contenido === false || $contenido === '') {
                throw new LlamaParseException('No se pudo leer el PDF guardado.');
            }

            $respuesta = $this->cliente()
                ->acceptJson()
                ->timeout((int) config('llamaparse.timeout_subida', 180))
                ->attach('file', $contenido, 'documento.pdf', ['Content-Type' => 'application/pdf'])
                ->post($this->url('/parse/upload'), [
                    'configuration' => json_encode($this->configuracion(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

            if (! $respuesta->successful()) {
                throw new LlamaParseException($this->errorDeRespuesta($respuesta));
            }

            $jobId = (string) ($respuesta->json('id') ?? '');

            if ($jobId === '') {
                throw new LlamaParseException('LlamaParse no devolvio el ID del trabajo.');
            }

            $documento->update([
                'job_id' => $jobId,
                'estado' => EstadoDocumentoLlamaparse::Procesando,
                'progreso' => 15,
                'progreso_mensaje' => 'En cola en LlamaParse',
                'respuesta' => $respuesta->json(),
            ]);
        } catch (Throwable $e) {
            $this->marcarError($documento, $e->getMessage());

            throw $e instanceof LlamaParseException
                ? $e
                : new LlamaParseException($e->getMessage(), 0, $e);
        }
    }

    public function consultar(DocumentoLlamaparse $documento): void
    {
        if (! $documento->estado->enProceso()) {
            return;
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        try {
            if (blank($documento->job_id)) {
                $this->iniciar($documento);

                return;
            }

            $respuesta = $this->cliente()
                ->acceptJson()
                ->timeout((int) config('llamaparse.timeout_consulta', 30))
                ->get($this->url('/parse/'.$documento->job_id), [
                    'expand' => 'markdown,items,xlsx_content_metadata,usage',
                ]);

            if (! $respuesta->successful()) {
                throw new LlamaParseException($this->errorDeRespuesta($respuesta));
            }

            $datos = $respuesta->json();
            $estado = strtoupper((string) data_get($datos, 'job.status', ''));

            if (in_array($estado, ['FAILED', 'CANCELLED'], true)) {
                $mensaje = (string) (data_get($datos, 'job.error_message') ?: 'LlamaParse no pudo procesar el PDF.');
                $this->marcarError($documento, $mensaje, $datos);

                return;
            }

            if ($estado === 'COMPLETED') {
                $this->guardarResultado($documento, $datos);

                return;
            }

            $documento->update([
                'estado' => EstadoDocumentoLlamaparse::Procesando,
                'progreso' => max(20, (int) $documento->progreso),
                'progreso_mensaje' => 'Procesando en LlamaParse'.($estado !== '' ? ' ('.$estado.')' : ''),
                'respuesta' => $datos,
            ]);
        } catch (ConnectionException $e) {
            // Un timeout o corte de red no invalida el trabajo: se mantiene en
            // proceso para que el siguiente sondeo vuelva a consultar el mismo job.
            $documento->update([
                'estado' => EstadoDocumentoLlamaparse::Procesando,
                'progreso_mensaje' => 'Sin respuesta de LlamaParse, reintentando...',
                'error' => null,
            ]);
        } catch (Throwable $e) {
            $this->marcarError($documento, $e->getMessage());
        }
    }

    public function reprocesar(DocumentoLlamaparse $documento): void
    {
        if ($documento->estado->enProceso()) {
            throw new LlamaParseException('El documento ya se esta procesando.');
        }

        $disk = (string) config('llamaparse.disk', 'local');

        if ($documento->xlsx_archivo) {
            Storage::disk($disk)->delete($documento->xlsx_archivo);
        }

        $documento->update([
            'estado' => EstadoDocumentoLlamaparse::Pendiente,
            'job_id' => null,
            'progreso' => 0,
            'progreso_mensaje' => 'Pendiente',
            'respuesta' => null,
            'markdown' => null,
            'items' => null,
            'xlsx_archivo' => null,
            'json' => null,
            'json_crudo' => null,
            'usage_credits' => null,
            'error' => null,
            'procesado_at' => null,
        ]);

        $this->iniciar($documento);
    }

    public function eliminar(DocumentoLlamaparse $documento): void
    {
        $disk = (string) config('llamaparse.disk', 'local');

        Storage::disk($disk)->delete($documento->archivo);

        if ($documento->xlsx_archivo) {
            Storage::disk($disk)->delete($documento->xlsx_archivo);
        }

        $documento->delete();
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function guardarResultado(DocumentoLlamaparse $documento, array $datos): void
    {
        $documento->update([
            'progreso' => 70,
            'progreso_mensaje' => 'Descargando XLSX de LlamaParse',
            'respuesta' => $datos,
        ]);

        $urlXlsx = (string) (data_get($datos, 'result_content_metadata.xlsx.presigned_url') ?? '');

        if ($urlXlsx === '') {
            throw new LlamaParseException(
                'LlamaParse completo el trabajo pero no devolvio XLSX. Verifica que tables_as_spreadsheet este habilitado.'
            );
        }

        $descarga = $this->cliente(false)
            ->timeout((int) config('llamaparse.timeout_descarga', 60))
            ->get($urlXlsx);

        if (! $descarga->successful() || $descarga->body() === '') {
            throw new LlamaParseException('No se pudo descargar el XLSX generado por LlamaParse.');
        }

        $disk = (string) config('llamaparse.disk', 'local');
        $base = trim((string) config('llamaparse.ruta', 'llamaparse'), '/');
        $rutaXlsx = $base.'/'.$documento->uuid.'/resultado.xlsx';

        Storage::disk($disk)->put($rutaXlsx, $descarga->body());

        $pathXlsx = Storage::disk($disk)->path($rutaXlsx);
        $json = $this->xlsx->documento($pathXlsx, $documento->nombre_original);
        $jsonCrudo = $this->xlsx->procesar($pathXlsx, $documento->nombre_original);

        $paginasMarkdown = data_get($datos, 'markdown.pages');
        $markdown = is_array($paginasMarkdown)
            ? $paginasMarkdown
            : (data_get($datos, 'markdown_full') ? ['full' => data_get($datos, 'markdown_full')] : null);

        $paginasItems = data_get($datos, 'items.pages');

        $documento->update([
            'estado' => EstadoDocumentoLlamaparse::Procesado,
            'progreso' => 100,
            'progreso_mensaje' => 'Procesado',
            'xlsx_archivo' => $rutaXlsx,
            'json' => $json,
            'json_crudo' => $jsonCrudo,
            'markdown' => $markdown,
            'items' => is_array($paginasItems) ? $paginasItems : null,
            'usage_credits' => data_get($datos, 'job.usage.credits'),
            'procesado_at' => now(),
            'error' => null,
        ]);
    }

    private function marcarError(DocumentoLlamaparse $documento, string $mensaje, ?array $datos = null): void
    {
        $documento->update([
            'estado' => EstadoDocumentoLlamaparse::Error,
            'progreso_mensaje' => 'Error al procesar',
            'error' => Str::limit($mensaje, 1000),
            'respuesta' => $datos ?? $documento->respuesta,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function configuracion(): array
    {
        return [
            'tier' => (string) config('llamaparse.tier', 'agentic'),
            'version' => (string) config('llamaparse.version', 'latest'),
            'output_options' => [
                'tables_as_spreadsheet' => [
                    'enable' => (bool) config('llamaparse.tables_as_spreadsheet', true),
                ],
                'markdown' => [
                    'tables' => [
                        'output_tables_as_markdown' => true,
                    ],
                ],
            ],
            'processing_options' => [
                'ocr_parameters' => [
                    'languages' => config('llamaparse.idiomas', ['es', 'en']),
                ],
            ],
        ];
    }

    private function cliente(bool $conToken = true): PendingRequest
    {
        $cliente = Http::withOptions([
            'verify' => (bool) config('llamaparse.verify_ssl', true),
        ]);

        return $conToken
            ? $cliente->withToken((string) config('llamaparse.api_key'))
            : $cliente;
    }

    private function url(string $ruta): string
    {
        return rtrim((string) config('llamaparse.base_url'), '/').$ruta;
    }

    private function errorDeRespuesta(Response $respuesta): string
    {
        $detalle = $respuesta->json('detail')
            ?? $respuesta->json('error')
            ?? $respuesta->json('message');

        if (is_array($detalle)) {
            $mensajes = [];

            foreach ($detalle as $item) {
                if (is_array($item)) {
                    $mensajes[] = (string) ($item['msg'] ?? json_encode($item, JSON_UNESCAPED_UNICODE));
                } else {
                    $mensajes[] = (string) $item;
                }
            }

            if ($mensajes !== []) {
                return implode(' | ', $mensajes);
            }
        }

        if (is_string($detalle) && trim($detalle) !== '') {
            return $detalle;
        }

        return 'LlamaParse respondio HTTP '.$respuesta->status();
    }
}
