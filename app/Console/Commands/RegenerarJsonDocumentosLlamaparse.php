<?php

namespace App\Console\Commands;

use App\Enums\EstadoDocumentoLlamaparse;
use App\Models\DocumentoLlamaparse;
use App\Services\LlamaParse\XlsxJsonService;
use App\Services\OrdenPagoAuditCompareService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Regenera el JSON de los documentos ya procesados a partir del XLSX que
 * LlamaParse dejo guardado en storage.
 *
 * Sirve cuando cambia la forma de leer el XLSX (por ejemplo, para agregar las
 * retenciones de la hoja "Valores"): no hace falta volver a enviar el PDF a
 * LlamaParse ni gastar credits.
 */
class RegenerarJsonDocumentosLlamaparse extends Command
{
    protected $signature = 'documentos-llamaparse:regenerar-json
        {--documento= : Id del documento a regenerar (por defecto, todos los procesados con XLSX guardado)}
        {--auditar : Vuelve a generar la auditoria de ordenes de pago de cada documento}
        {--dry-run : Muestra lo que se haria sin guardar los cambios}';

    protected $description = 'Regenera el JSON de los documentos procesados desde el XLSX guardado en storage (no llama a LlamaParse)';

    public function handle(): int
    {
        $documentoId = $this->option('documento');
        $auditar     = (bool) $this->option('auditar');
        $dryRun      = (bool) $this->option('dry-run');

        $documentos = DocumentoLlamaparse::query()
            ->where('estado', EstadoDocumentoLlamaparse::Procesado->value)
            ->whereNotNull('xlsx_archivo')
            ->when($documentoId !== null, fn ($query) => $query->whereKey((int) $documentoId))
            ->orderBy('id')
            ->get();

        if ($documentos->isEmpty()) {
            $this->warn('No hay documentos procesados con XLSX guardado.');

            return self::SUCCESS;
        }

        $disk = (string) config('llamaparse.disk', 'local');
        $xlsx = app(XlsxJsonService::class);
        $errores = 0;

        foreach ($documentos as $documento) {
            $path = Storage::disk($disk)->path($documento->xlsx_archivo);

            if (! is_file($path)) {
                $this->error("Documento {$documento->id}: no se encuentra el XLSX ({$documento->xlsx_archivo}).");
                $errores++;

                continue;
            }

            try {
                $json     = $xlsx->documento($path, $documento->nombre_original, $documento->markdown);
                $jsonCrudo = $xlsx->procesar($path, $documento->nombre_original);
            } catch (Throwable $e) {
                $this->error("Documento {$documento->id}: no se pudo leer el XLSX. ".$e->getMessage());
                $errores++;

                continue;
            }

            $cabecera = $json['cabecera'] ?? [];
            $this->line("Documento {$documento->id} ({$documento->nombre_original}): ".$this->retenciones($cabecera));

            if (! $dryRun) {
                $documento->update([
                    'json'      => $json,
                    'json_crudo' => $jsonCrudo,
                ]);
            }

            if (! $auditar) {
                continue;
            }

            $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento->refresh(), false);

            if (! $resultado['success']) {
                $this->error("Documento {$documento->id}: la auditoria no se genero. ".$resultado['message']);
                $errores++;

                continue;
            }

            $this->info("Documento {$documento->id}: auditoria {$resultado['cabecera_id']} regenerada.");
        }

        if ($errores > 0) {
            $this->error("Documentos con errores: {$errores}.");
        }

        if ($dryRun) {
            $this->info('Dry-run: no se guardo nada.');
        }

        return $errores > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $cabecera
     */
    private function retenciones(array $cabecera): string
    {
        $gastos = null;

        if (isset($cabecera['retencion_gastos_11_centavos']) || isset($cabecera['retencion_gastos_5_centavos'])) {
            $gastos = (isset($cabecera['retencion_gastos_11_centavos']) ? (int) $cabecera['retencion_gastos_11_centavos'] : 0)
                + (isset($cabecera['retencion_gastos_5_centavos']) ? (int) $cabecera['retencion_gastos_5_centavos'] : 0);
        }

        return sprintf(
            'caja=%s gastos=%s ib=%s',
            $this->centavos($cabecera['retencion_caja_centavos'] ?? null),
            $this->centavos($gastos),
            $this->centavos($cabecera['retencion_ib_centavos'] ?? null)
        );
    }

    /**
     * @param  mixed  $centavos
     */
    private function centavos(mixed $centavos): string
    {
        if ($centavos === null) {
            return 'sin dato';
        }

        return number_format(((int) $centavos) / 100, 2, ',', '.');
    }
}
