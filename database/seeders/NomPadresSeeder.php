<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NomPadresSeeder extends Seeder
{
    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run()
    {
        $padres = [
            ['nombre' => 'General', 'tipo' => 'A'],
            ['nombre' => 'IOMA', 'tipo' => 'A'],
        ];

        DB::table('nom_padres')->insert($padres);
    }
}