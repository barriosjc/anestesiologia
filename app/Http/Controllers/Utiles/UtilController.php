<?php

namespace App\Http\Controllers\Utiles;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\View\View;

class UtilController extends Controller
{
    public function runMigrationAndSeeder(): View
    {
        // Verificar que no esté en producción (opcional)
        // if (app()->environment('production')) {
        //     abort(403, 'No permitido en producción');
        // }

        $result = [
            'status' => 'success',
            'messages' => [],
            'outputs' => []
        ];

        try {
            Artisan::call('migrate');
            $migrationOutput = Artisan::output();
            $result['messages'][] = 'Migraciones ejecutadas con éxito';
            $result['outputs'][] = $migrationOutput;

            Artisan::call('db:seed');
            $seederOutput = Artisan::output();
            $result['messages'][] = 'Seeder ejecutado con éxito';
            $result['outputs'][] = $seederOutput;

        } catch (\Exception $e) {
            $result['status'] = 'error';
            $result['messages'][] = 'Error al ejecutar el proceso: ' . $e->getMessage();
            $result['outputs'][] = $e->getMessage();
        }

        return view('utiles.migration', $result);
    }
}