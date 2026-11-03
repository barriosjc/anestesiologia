<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportCoberturasCodUom extends Command
{
    protected $signature = 'coberturas:import-cod-uom {file? : Ruta al archivo Excel}';

    protected $description = 'Importa cod_uom desde un archivo Excel a la tabla coberturas, usando la columna id como referencia. Omite filas donde cod_uom esté vacío.';

    public function handle(): int
    {
        $filePath = $this->argument('file') ?? storage_path('app/coberturas.xlsx');

        if (! file_exists($filePath)) {
            $this->error("No se encontró el archivo: {$filePath}");
            $this->info('Uso: php artisan coberturas:import-cod-uom /ruta/al/archivo.xlsx');

            return self::FAILURE;
        }

        $this->info("Procesando archivo: {$filePath}");

        // Read the Excel file
        $reader = IOFactory::createReaderForFile($filePath);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow();

        // Read headers using letter-based coordinates (column A, B, C, D)
        $colLetters = range('A', 'Z');
        $headers = [];
        for ($i = 0; $i < 26; $i++) {
            $col = $colLetters[$i];
            $val = $sheet->getCell($col . '1')->getValue();
            if ($val === null || $val === '') {
                break;
            }
            $headers[$col] = trim((string) $val);
        }

        $this->info("Columnas detectadas:");
        foreach ($headers as $col => $header) {
            $this->info("  {$col}: {$header}");
        }

        // Map column letters for 'id' and 'cod_uom' (handle 'cod uom' with space)
        $idCol = null;
        $codUomCol = null;
        foreach ($headers as $col => $header) {
            $headerLower = strtolower($header);
            if ($headerLower === 'id') {
                $idCol = $col;
            }
            // Accept both 'cod_uom' and 'cod uom'
            if ($headerLower === 'cod_uom' || $headerLower === 'cod uom') {
                $codUomCol = $col;
            }
        }

        if ($idCol === null || $codUomCol === null) {
            $this->error("El archivo debe tener columnas 'id' y 'cod_uom' (o 'cod uom') en la primera fila.");
            $this->info("Columnas detectadas: " . implode(', ', $headers));

            return self::FAILURE;
        }

        $this->info("Columna 'id' en posición: {$idCol}");
        $this->info("Columna 'cod_uom' en posición: {$codUomCol}");

        $updated = 0;
        $skippedEmpty = 0;
        $notFound = 0;
        $errors = 0;
        $errorDetails = [];

        // Process data rows starting from row 2
        $bar = $this->output->createProgressBar($highestRow - 1);
        $bar->start();

        for ($row = 2; $row <= $highestRow; $row++) {
            $id = $sheet->getCell($idCol . $row)->getValue();
            $codUom = $sheet->getCell($codUomCol . $row)->getValue();

            $id = trim((string) $id);
            if ($id === '' || $id === '0') {
                $bar->advance();
                continue;
            }

            $codUom = trim((string) $codUom);
            if ($codUom === '') {
                $skippedEmpty++;
                $bar->advance();
                continue;
            }

            // Check if the cobertura exists (excluding soft-deleted)
            $exists = DB::table('coberturas')->where('id', $id)->exists();
            if (! $exists) {
                $notFound++;
                $errorDetails[] = "ID {$id} no existe en coberturas";
                $bar->advance();
                continue;
            }

            // Update the cobertura
            try {
                DB::table('coberturas')
                    ->where('id', $id)
                    ->update(['cod_uom' => $codUom]);
                $updated++;
            } catch (\Throwable $e) {
                $errors++;
                $errorDetails[] = "Error al actualizar ID {$id}: " . $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);
        $this->info("=== Resumen ===");
        $this->info("Registros actualizados: {$updated}");
        $this->info("Filas omitidas (cod_uom vacío): {$skippedEmpty}");
        $this->info("IDs no encontrados en la tabla: {$notFound}");
        if ($errors > 0) {
            $this->error("Errores: {$errors}");
        }
        foreach ($errorDetails as $detail) {
            $this->line("  • {$detail}");
        }

        return self::SUCCESS;
    }
}
