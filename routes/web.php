<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/main', function () {
//     return view('layouts.main');
// });

Auth::routes();

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\GrupalController;
use App\Http\Controllers\GrupalUserController;
use App\Http\Controllers\JerarquiumController;
use App\Http\Controllers\PeriodoController; 
use App\Http\Controllers\OpcionController;
use App\Http\Controllers\encuestas\EncuestaController;

Route::get("tabla", function(){
    return view('layouts.ejemplo');
});

Route::group(['middleware' => 'auth'], function() {
    Route::get('/', function () {
        return view('layouts.main');
    });    
    Route::resources( ['empresas' => EmpresaController::class, 
                    'grupals' => GrupalController::class,
                    'grupalusers' => grupalUserController::class,
                    'jerarquias' => JerarquiumController::class,
                    'periodos' => PeriodoController::class,
                    'opcion' => OpcionController::class,
                    ]
    );
    Route::get('respuesta', [EncuestaController::class, 'index'])->name('respuesta');
    Route::post('respuesta/store', [EncuestaController::class, 'store'])->name('respuesta.store');
    Route::get('encuesta/nueva', [EncuestaController::class, 'create'])->name('encuesta.create');
    Route::post('encuesta/store', [EncuestaController::class, 'create_store'])->name('encuesta.store');
});
