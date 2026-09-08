<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshViews extends Command
{
    protected $signature = 'views:refresh';

    protected $description = 'Recrea las vistas v_* usando la conexión actual, corrigiendo el DEFINER y el nombre de esquema';

    public function handle(): int
    {
        $file = database_path('vistasSql');

        if (! file_exists($file)) {
            $this->error("No existe el archivo {$file}");

            return self::FAILURE;
        }

        $content = file_get_contents($file);

        $schema = DB::getDatabaseName();
        $this->info("Base de datos: {$schema}");

        $statements = preg_split('/^\s*--.*$/m', $content);

        $count = 0;
        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement === '' || ! str_starts_with($statement, 'create')) {
                continue;
            }

            $sql = preg_replace('/`[^`]+`\.`([^`]+)`/', "`{$schema}`.`\$1`", $statement);

            try {
                DB::statement($sql);
                $count++;
                $this->line("OK: " . substr($sql, 0, 60) . '...');
            } catch (\Throwable $e) {
                $this->error("Falló: " . $e->getMessage());
            }
        }

        $this->info("Vistas recreadas: {$count}");

        return self::SUCCESS;
    }
}
