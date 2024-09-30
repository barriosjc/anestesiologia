<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\cargas\ParteController;
use App\Http\Controllers\seguridad\RoleController;
use App\Http\Controllers\entidades\CentroController;
use App\Http\Controllers\seguridad\ProfileController;
use App\Http\Controllers\seguridad\UsuarioController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\entidades\PacienteController;
// use App\Http\Controllers\seguridad\Usuario0Controller;
use App\Http\Controllers\produccion\ConsumoController;
use App\Http\Controllers\seguridad\PermisosController;
use App\Http\Controllers\entidades\CoberturaController;
use App\Http\Controllers\entidades\NomencladorController;
use App\Http\Controllers\entidades\ProfesionalController;
use App\Http\Controllers\entidades\PreciosListasController;
use App\Http\Controllers\entidades\PreciosValoresController;

// Auth::routes();
Route::match(['get'],'login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('errores', function () {
    return view('errors.404');
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

        //perfil de usuario
        Route::get('profile/{id}/editar', [ProfileController::class, 'index'])->name('profile');
        Route::post('foto/profile/guardar', [ProfileController::class, 'foto'])->name('profile.foto');
        Route::post('profile', [ProfileController::class, 'save'])->name('profile.save');
        Route::get('profile/{id}/readonly', [ProfileController::class, 'readonly'])->name('profile.readonly');
        Route::get('roles/combos/json', [RoleController::class, 'roles_json'])->name('roles.json');

        Route::group(['middleware' => ['permission:adm_partes']], function () {
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
            Route::post('consumos/procesar', [ConsumoController::class, 'aProcesar'])->name('consumos.aprocesar');
        });

        Route::group(['middleware' => ['permission:adm_consumos']], function () {
            Route::get('nomenclador/valores/listas', [PreciosValoresController::class, 'index'])->name('nomenclador.valores.listas');
            Route::get('nomenclador/filtrar', [PreciosValoresController::class, 'filtrar'])->name('nomenclador.valores.filtrar');
            Route::get('nomeclador/valores/nuevos', [PreciosValoresController::class, 'nuevo'])->name('nomenclador.valores.nuevo');
            Route::post('nomeclador/valor/guardar', [PreciosValoresController::class, 'guardar'])->name('nomenclador.valor.guardar');
            Route::delete('nomenclador/valores/borrar/{id}', [PreciosValoresController::class, 'borrar'])->name('nomenclador.valores.borrar');
            // Route::post('nomeclador/valores/buscar', [PreciosValoresController::class, 'valores_buscar'])->name('nomenclador.valores.buscar');

            Route::get('nomenclador/listas', [PreciosListasController::class, 'index'])->name('nomenclador.listas.listas');
            Route::get('nomenclador/listas/nuevos', [PreciosListasController::class, 'nuevo'])->name('nomenclador.listas.nuevo');
            Route::delete('nomenclador/listas/borrar/{id}', [PreciosListasController::class, 'borrar'])->name('nomenclador.listas.borrar');
            Route::get('nomenclador/listas/filtrar', [PreciosListasController::class, 'filtrar'])->name('nomenclador.listas.filtrar');
            Route::get('nomenclador/listas/modificar', [PreciosListasController::class, 'modificar'])->name('nomenclador.listas.modificar');
            Route::post('nomeclador/lista/guardar', [PreciosValoresController::class, 'guardar'])->name('nomenclador.lista.guardar');

            // Route::get('consumos/partes', [ConsumoController::class, 'partes'])->name('consumos.partes');
            Route::get('consumos/partes/filtrar', [ConsumoController::class, 'parte_filtrar'])->name('consumos.partes.filtrar');
            Route::get('consumos/cargar/{id}', [ConsumoController::class, 'cargar'])->name('consumos.cargar');
            Route::post('consumos/valor/buscar', [ConsumoController::class, 'valor_buscar'])->name('consumos.valor.buscar');
            Route::post('consumos/guardar', [ConsumoController::class, 'guardar'])->name('consumos.guardar');
            Route::delete('consumos/borrar/{id}', [ConsumoController::class, 'destroy'])->name('consumos.borrar');
            Route::post('consumos/observar', [ConsumoController::class, 'observar'])->name('consumos.observar');

            Route::get('consumos/rendicion/filtrar', [ConsumoController::class, 'rendicion_filtrar'])->name('consumo.rendiciones.filtrar');
            Route::post('consumos/rendicion/guardar', [ConsumoController::class, 'rendicion_store'])->name('consumo.rendiciones.store');
            Route::get('consumos/rendicion/listado', [ConsumoController::class, 'rendicion_listado'])->name('consumo.rendiciones.listado');
            Route::post('consumos/rendicion/listado/generar', [ConsumoController::class, 'rendicion_listar'])->name('consumo.rendiciones.listar');
            Route::post('consumos/rendicion/estados', [ConsumoController::class, 'rendicion_estados'])->name('consumo.rendiciones.estados');
            Route::post('consumos/rendicion/revalorizar', [ConsumoController::class, 'rendicion_revalorizar'])->name('consumo.rendiciones.revalorizar');
        });

        Route::group(['middleware' => ['permission:adm_entidades']], function () {
            Route::resources(
                [
                    'profesionales' => ProfesionalController::class,
                    'centros' => CentroController::class,
                    'coberturas' => CoberturaController::class,
                ]
            );
            Route::get('profesional/documentacion/{id}', [ProfesionalController::class, 'cargar_docum'])->name('profesional.cargar.documentacion');
            Route::post('profesional/documentacion/guardar', [ProfesionalController::class, 'guardar_docum'])->name('profesional.guardar.documentacion');
            Route::delete('profesional/documentacion/borrar/{id}', [ProfesionalController::class, 'borrar_docum'])->name('profesional.borrar.documentacion');
            Route::get('profesional/documentacion/download/{id}', [ProfesionalController::class, 'download_docum'])->name('profesional.download.documentacion');
        });
        
        Route::group(['middleware' => ['permission:adm_permisos']], function () {
            Route::resources([
                'usuario' => UsuarioController::class,
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



    });
});
