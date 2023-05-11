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
use App\Http\Controllers\encuestas\RespuestaController;

Route::get("tabla", function(){
    return view('layouts.ejemplo');
});
Route::get("create_wiz", function(){
    return view('encuestas.create_wiz');
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
    Route::get('respuesta', [RespuestaController::class, 'index'])->name('respuesta');
    Route::post('respuesta/store', [respuestaController::class, 'store'])->name('respuesta.store');
    Route::group(['middleware' => ['can:creacion de encuestas']], function () {
        Route::get('encuesta/nueva', [EncuestaController::class, 'create'])->name('encuesta.create');
        Route::post('encuesta/store', [EncuestaController::class, 'create_store'])->name('encuesta.store');
        Route::post('periodo/store', [EncuestaController::class, 'periodo_store'])->name('periodo.store');    
        Route::post('opcion/store', [EncuestaController::class, 'opcion_store'])->name('opcion.store');
    });
});
