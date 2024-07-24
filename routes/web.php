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

Auth::routes();

use App\Http\Controllers\cargas\ParteController;
use App\Http\Controllers\seguridad\RoleController;
use App\Http\Controllers\seguridad\ProfileController;
use App\Http\Controllers\seguridad\UsuarioController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\entidades\PacienteController;
use App\Http\Controllers\produccion\ConsumoController;
use App\Http\Controllers\seguridad\PermisosController;
use App\Http\Controllers\entidades\NomencladorController;
use App\Http\Controllers\entidades\ProfesionalController;

Route::get('/tables', function () {

    return view('tables');
});

Route::get('/guard', function () {

    session('empresa')->uri = 'web';

    return redirect()->route('login');

});

Route::get('login/restablecer', [ResetPasswordController::class, 'restablecer'])->name('login.restablecer');
Route::post('login/email', [ResetPasswordController::class, 'email'])->name('login.email');


Route::group(['middleware' => 'auth'], function () {
    // Route::get('empresas/usuarios/combos', [ProfileController::class, 'usuarios_jefes'])->name('empresas.usuarios');
    Route::get('password/profile', [ProfileController::class, 'password'])->name('profile.password');
    Route::post('pasword/profile', [ProfileController::class, 'save_password'])->name('profile.password.save');

    Route::middleware('IngresoInicialMiddleware')->group(function () {
        $guard = 'web';

        Route::get('/', function () {
            return view('layouts.main');
        })->name('main');


        Route::get('partes', [ParteController::class, 'index'])->name('partes_cab.index');
        Route::get('partes/create', [ParteController::class, 'create'])->name('partes_cab.create');
        Route::post('partes/store', [ParteController::class, 'store'])->name('partes_cab.store');
        Route::delete('partes/delete/{id}', [ParteController::class, 'destroy'])->name('partes_cab.destroy');
        Route::get('partes/edit/{id}', [ParteController::class, 'edit'])->name('partes_cab.edit');
        
        Route::get('partes/det/create/{id}', [ParteController::class, 'create_det'])->name('partes_det.create');
        Route::post('partes/det/store', [ParteController::class, 'store_det'])->name('partes_det.store');
        Route::delete('partes/det/delete/{id}', [ParteController::class, 'destroy_det'])->name('partes_det.destroy');
        Route::get('partes/det/edit/{id}', [ParteController::class, 'edit_det'])->name('partes_det.edit');
        Route::get('partes/det/download/{id}', [ParteController::class, 'download'])->name('partes_det.download');
        
        Route::get('pacientes/buscar', [PacienteController::class, 'buscar'])->name('pacientes.buscar');

        Route::get('nomenclador/valores', [NomencladorController::class, 'valores'])->name('nomenclador.valores.listado');
        Route::get('nomenclador/filtrar', [NomencladorController::class, 'valores_filtrar'])->name('nomenclador.valores.filtrar');
        Route::post('nomeclador/valores/nuevos', [NomencladorController::class, 'valores_nuevos'])->name('nomenclador.valores.nuevo');
        Route::post('nomeclador/valor/guardar', [NomencladorController::class, 'valor_guardar'])->name('nomenclador.valor.guardar');
        Route::delete('nomenclador/valores/borrar/{id}', [NomencladorController::class, 'valores_borrar'])->name('nomenclador.valores.borrar');
        Route::post('nomeclador/valores/buscar', [NomencladorController::class, 'valores_buscar'])->name('nomenclador.valores.buscar');

        // Route::get('consumos/partes', [ConsumoController::class, 'partes'])->name('consumos.partes');
        Route::get('consumos/partes/filtrar', [ConsumoController::class, 'parte_filtrar'])->name('consumos.partes.filtrar');
        Route::get('consumos/cargar/{id}', [ConsumoController::class, 'cargar'])->name('consumos.cargar');
        Route::post('consumos/valor/buscar', [ConsumoController::class, 'valor_buscar'])->name('consumos.valor.buscar');
        Route::post('consumos/guardar', [ConsumoController::class, 'guardar'])->name('consumos.guardar');

        Route::resources(
            [
                'profesionales' => ProfesionalController::class,
            ]
        );
        //perfil de usuario
        Route::get('profile/{id}/editar', [ProfileController::class, 'index'])->name('profile');
        Route::post('foto/profile/guardar', [ProfileController::class, 'foto'])->name('profile.foto');
        Route::post('profile', [ProfileController::class, 'save'])->name('profile.save');
        Route::get('profile/{id}/readonly', [ProfileController::class, 'readonly'])->name('profile.readonly');
        // Route::group(['middleware' => ['can:ABM Usuarios']], function () use ($guard) {
            // if (Auth()->user()->hasPermissionTo('ABM Usuarios', $guard)) {
            //     dd("valido ok", $guard);
            // }
            Route::get('roles/combos/json', [RoleController::class, 'roles_json'])->name('roles.json');
            Route::resources([
                'usuario' => UsuarioController::class,
            ]);
        // });
        //Route::group(['middleware' => ['can:seguridad']], function () {
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
        //});



    });
});
