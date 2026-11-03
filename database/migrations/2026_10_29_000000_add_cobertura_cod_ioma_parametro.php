<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('parametros')->updateOrInsert(
            ['nombre' => 'COBERTURA_COD_IOMA'],
            [
                'valor'      => '78',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('parametros')->where('nombre', 'COBERTURA_COD_IOMA')->delete();
    }
};
