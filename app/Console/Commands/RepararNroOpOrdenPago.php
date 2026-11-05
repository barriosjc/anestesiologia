<?php

namespace App\Console\Commands;

use App\Models\DocumentoLlamaparse;
use App\Models\OrdenPagoAuditCab;
use App\Services\LlamaParse\NumeroOrdenPagoExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RepararNroOpOrdenPago extends Command
{
    protected $signature = 'ordenes-pagos:reparar-nro-op {--dry-run : Muestra los cambios sin guardarlos}';

    protected $description = 'Completa el nro_op de las auditorias que quedaron con el fallback DOC-<id>, usando el markdown del documento';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $ordenes = OrdenPagoAuditCab::query()
            ->whereNotNull('documento_llamaparse_id')
            // Primero las mas nuevas: si dos documentos traen la misma OP, el
            // numero limpio queda en la auditoria mas reciente.
            ->orderByDesc('id')
            ->get();

        $cambios = 0;
        $asignados = [];

        foreach ($ordenes as $orden) {
            // Solo se tocan las filas que cayeron en el fallback "DOC-<id>":
            // un numero real cargado a mano no se reemplaza.
            if (preg_match('/^DOC-\d+$/', (string) $orden->nro_op) !== 1) {
                continue;
            }

            $documento = DocumentoLlamaparse::query()->find($orden->documento_llamaparse_id);
            $numero = $documento ? NumeroOrdenPagoExtractor::desdeMarkdown($documento->markdown) : null;

            if ($numero === null) {
                $this->line("id {$orden->id}: sin numero de OP en el markdown (queda {$orden->nro_op}).");

                continue;
            }

            $nuevo = $this->nroOpUnico($numero, (int) $orden->documento_llamaparse_id, $asignados);
            $anterior = $orden->nro_op;

            if (! $dryRun) {
                $orden->update(['nro_op' => $nuevo]);
            }

            $asignados[] = $nuevo;

            $this->info("id {$orden->id}: {$anterior} -> {$nuevo}".($dryRun ? ' (dry-run)' : ''));

            $cambios++;
        }

        $this->info($dryRun
            ? "Cambios detectados: {$cambios} (no se guardo nada)."
            : "Ordenes actualizadas: {$cambios}.");

        return self::SUCCESS;
    }

    /**
     * Mismo criterio que OrdenPagoAuditCompareService: si otro documento ya
     * usa ese numero, se agrega el id del documento para respetar el unico.
     *
     * @param  array<int, string>  $asignados  numeros ya tomados en esta corrida
     */
    private function nroOpUnico(string $nroOp, int $documentoId, array $asignados): string
    {
        $ocupado = in_array($nroOp, $asignados, true)
            || OrdenPagoAuditCab::query()
                ->where('nro_op', $nroOp)
                ->where(function ($query) use ($documentoId) {
                    $query->whereNull('documento_llamaparse_id')
                        ->orWhere('documento_llamaparse_id', '!=', $documentoId);
                })
                ->exists();

        return $ocupado ? Str::limit($nroOp, 12, '').'-'.$documentoId : $nroOp;
    }
}
