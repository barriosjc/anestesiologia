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

use App\Http\Controllers\entidades\EmpresaController;
use App\Http\Controllers\GrupalController;
use App\Http\Controllers\GrupalUserController;
use App\Http\Controllers\JerarquiumController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\entidades\OpcionController;
use App\Http\Controllers\encuestas\EncuestaController;
use App\Http\Controllers\encuestas\RespuestaController;
use App\Http\Controllers\seguridad\PermisosController;
use App\Http\Controllers\seguridad\ProfileController;
use App\Http\Controllers\seguridad\RoleController;
use App\Http\Controllers\seguridad\UsuarioController;
use App\Http\Controllers\encuestas\ReconocimientosController;
use App\Models\User;

Route::get('/tables', function () {

    return view('tables');
});


Route::get('portal/{empresa}', [EmpresaController::class, 'entorno'])->name('entorno');

Route::group(['middleware' => 'auth'], function () {
    Route::group(['middleware' => ['role:super-admin']], function () {
        Route::get('empresa/select}', [EmpresaController::class, 'select'])->name('empresa.select');
        Route::post('empresa/set}', [EmpresaController::class, 'set'])->name('empresa.set');
    });
    Route::get('empresas/usuarios/combos', [ProfileController::class, 'usuarios_jefes'])->name('empresas.usuarios');
    Route::get('password/profile', [ProfileController::class, 'password'])->name('profile.password');
    Route::post('pasword/profile', [ProfileController::class, 'save_password'])->name('profile.password.save');

    Route::middleware('IngresoInicialMiddleware')->group(function () {
        Route::get('/', function () {
            return view('layouts.main');
        })->name('main');

        Route::get("create_wiz", function () {
            return view('encuestas.create_wiz');
        });

        Route::get('reconocimientos/realizados', [ReconocimientosController::class, 'realizados'])->name('reconocimientos.realizados');
        Route::get('reconocimientos/recibidos', [ReconocimientosController::class, 'recibidos'])->name('reconocimientos.recibidos');

        Route::group(['middleware' => ['can:abm empresas']], function () {
            Route::resources(['empresas' => EmpresaController::class,]);
        });

        Route::resources(
            [
                'grupals' => GrupalController::class,
                'grupalusers' => grupalUserController::class,
                'jerarquias' => JerarquiumController::class,
                'periodos' => PeriodoController::class,
                'opcion' => OpcionController::class,
            ]
        );
        //perfil de usuario
        Route::get('profile/{id}/editar', [ProfileController::class, 'index'])->name('profile');
        Route::post('foto/profile/guardar', [ProfileController::class, 'foto'])->name('profile.foto');

        Route::group(['middleware' => ['can:abm usuarios']], function () {
            Route::get('empresas/roles/combos', [RoleController::class, 'roles_empresas'])->name('empresas.roles');
            Route::post('profile', [ProfileController::class, 'save'])->name('profile.save');
            Route::resources([
                'usuario' => UsuarioController::class,
            ]);
        });
        Route::group(['middleware' => ['can:seguridad']], function () {
            Route::resources([
                'roles' => RoleController::class,
                'permisos' => permisosController::class,
            ]);
            Route::get('usuario/{id}/roles/{rolid}/{tarea}', [UsuarioController::class, 'roles']);
            Route::get('usuario/{id}/roles', [UsuarioController::class, 'roles'])->name('usuarios.grupos');
            Route::get('usuario/{id}/permisos/{perid}/{tarea}', [UsuarioController::class, 'permisos']);
            Route::get('usuario/{id}/permisos', [UsuarioController::class, 'permisos']);

            Route::get('roles/{id}/permisos/{perid}/{tarea}', [RoleController::class, 'permisos']);
            Route::get('roles/{id}/permisos', [RoleController::class, 'permisos']);
            Route::get('roles/{id}/usuarios/{usuid}/{tarea}', [RoleController::class, 'usuarios']);
            Route::get('roles/{id}/usuarios', [RoleController::class, 'usuarios']);

            Route::get('permisos/{id}/usuarios/{usuid}/{tarea}', [permisosController::class, 'usuarios']);
            Route::get('permisos/{id}/usuarios', [permisosController::class, 'usuarios'])->name('permisos.usuarios');
            Route::get('permisos/{id}/roles/{rolid}/{tarea}', [permisosController::class, 'roles']);
            Route::get('permisos/{id}/roles', [permisosController::class, 'roles'])->name('permisos.grupos');
        });

        Route::get('respuesta', [RespuestaController::class, 'index'])->name('respuesta');
        Route::post('respuesta/store', [respuestaController::class, 'store'])->name('respuesta.store');
        Route::group(['middleware' => ['can:creacion de encuestas']], function () {
            Route::get('encuesta/nueva', [EncuestaController::class, 'create'])->name('encuesta.create');
            Route::post('encuesta/store', [EncuestaController::class, 'create_store'])->name('encuesta.store');
            Route::post('periodo/store', [EncuestaController::class, 'periodo_store'])->name('periodo.store');
            Route::post('opciones/store', [EncuestaController::class, 'opcion_store'])->name('opciones.store');
        });
    });
});
