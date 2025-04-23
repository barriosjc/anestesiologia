<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Utiles\UtilController;
use App\Http\Controllers\cargas\ParteController;
use App\Http\Controllers\seguridad\RoleController;
use App\Http\Controllers\entidades\CentroController;
use App\Http\Controllers\entidades\ParametroController;
use App\Http\Controllers\seguridad\ProfileController;
use App\Http\Controllers\seguridad\UsuarioController;
use App\Http\Controllers\Auth\ResetPasswordController;
// use App\Http\Controllers\seguridad\Usuario0Controller;
use App\Http\Controllers\entidades\NomPadreController;
use App\Http\Controllers\entidades\PacienteController;
use App\Http\Controllers\produccion\ConsumoController;
use App\Http\Controllers\seguridad\PermisosController;
use App\Http\Controllers\entidades\CoberturaController;
use App\Http\Controllers\entidades\NomencladorController;
use App\Http\Controllers\entidades\ProfesionalController;
use App\Http\Controllers\entidades\PreciosListasController;
use App\Http\Controllers\entidades\PreciosValoresController;
use App\Http\Controllers\entidades\PresupuestoCabController;
use App\Http\Controllers\entidades\PresupuestoDetController;
use App\Http\Controllers\entidades\NomPracticasEstudioController;

// Auth::routes();
Route::match(['get'], 'login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('login/restablecer', [ResetPasswordController::class, 'restablecer'])->name('login.restablecer');
Route::post('login/email', [ResetPasswordController::class, 'email'])->name('login.email');


Route::group(['middleware' => 'auth'], function () {
    Route::get('/run-migration', [UtilController::class, 'runMigrationAndSeeder'])
                ->middleware(['role:super-admin']);
    // Route::get('empresas/usuarios/combos', [ProfileController::class, 'usuarios_jefes'])->name('empresas.usuarios');
    Route::get('password/profile', [ProfileController::class, 'password'])->name('profile.password');
    Route::post('pasword/profile', [ProfileController::class, 'savePassword'])->name('profile.password.save');

    Route::middleware('IngresoInicialMiddleware')->group(function () {
        $guard = 'web';

        Route::get('/', function () {
            return view('varios.novedades');
        })->name('main');

        //perfil de usuario
        Route::get('profile/{id}/editar', [ProfileController::class, 'index'])->name('profile');
        Route::post('foto/profile/guardar', [ProfileController::class, 'foto'])->name('profile.foto');
        Route::post('profile', [ProfileController::class, 'save'])->name('profile.save');
        Route::get('profile/{id}/readonly', [ProfileController::class, 'readonly'])->name('profile.readonly');
        Route::get('roles/combos/json', [RoleController::class, 'rolesJson'])->name('roles.json');

        Route::group(['middleware' => ['permission:adm_partes']], function () {
            Route::get('partes/calendar', [ParteController::class, 'calendar'])->name('partes_cab.calendar');
            Route::post('partes/calendar/guardar', [ParteController::class, 'calendarGuardar'])->name('partes_cab.calendar.guardar');
            
            Route::get('partes/create', [ParteController::class, 'create'])->name('partes_cab.create');
            Route::post('partes/store', [ParteController::class, 'store'])->name('partes_cab.store');
            Route::delete('partes/delete/{id}', [ParteController::class, 'destroy'])->name('partes_cab.destroy');
            Route::get('partes/edit/{id}', [ParteController::class, 'edit'])->name('partes_cab.edit');
            Route::post('partes/datos/obtener', [ParteController::class, 'getData'])->name('partes_cab.obtener');
            Route::get('partes/filtrar', [ParteController::class, 'filtrar'])->name('partes_cab.filtrar');

            Route::get('partes/det/create/{id}', [ParteController::class, 'createDet'])->name('partes_det.create');
            Route::post('partes/det/store', [ParteController::class, 'storeDet'])->name('partes_det.store');
            Route::delete('partes/det/delete/{id}', [ParteController::class, 'destroyDet'])->name('partes_det.destroy');
            Route::get('partes/det/edit/{id}', [ParteController::class, 'edit_det'])->name('partes_det.edit');
            Route::get('partes/det/download/{id}', [ParteController::class, 'download'])->name('partes_det.download');
            
            Route::get('pacientes/buscar', [PacienteController::class, 'buscar'])->name('pacientes.buscar');
            Route::post('consumos/procesar', [ConsumoController::class, 'aProcesar'])->name('consumos.aprocesar');
        });

        Route::group(['middleware' => ['permission:adm_consumos']], function () {
            Route::get('nomencladores/listar', [NomPadreController::class, 'index'])->name('nom_padres.index');
            Route::get('nomencladores/create', [NomPadreController::class, 'create'])->name('nom_padres.create');
            Route::post('nomencladores/store', [NomPadreController::class, 'store'])->name('nom_padres.store');
            
            // Route::get('nomencladores/anestisiologia', [NomencladorController::class, 'index'])->name('nomenclador.valores');
            Route::post('nomenclador/buscar', [NomencladorController::class, 'buscarCodDesc'])->name('nomenclador.buscar.coddesc');
            
            Route::get('nomenclador/valores/listas', [PreciosValoresController::class, 'index'])->name('nomenclador.valores.listas');
            Route::get('nomenclador/valores/filtrar', [PreciosValoresController::class, 'filtrar'])->name('nomenclador.valores.filtrar');
            Route::post('nomenclador/valores/grupo/nuevo', [PreciosValoresController::class, 'nuevoGrupo'])->name('nomenclador.valores.grupo.nuevo');
            Route::post('nomenclador/valores/guardar', [PreciosValoresController::class, 'guardarGrupo'])->name('nomenclador.valores.grupo.guardar');
            Route::delete('nomenclador/valor/borrar/{id}', [PreciosValoresController::class, 'borrar'])->name('nomenclador.valor.borrar');
            Route::get('nomenclador/valor/nuevo', [PreciosValoresController::class, 'nuevo'])->name('nomenclador.valor.nuevo');
            Route::post('nomenclador/valor/modificar', [PreciosValoresController::class, 'modificar'])->name('nomenclador.valor.modificar');
            Route::post('nomenclador/valor/guardar', [PreciosValoresController::class, 'guardar'])->name('nomenclador.valor.guardar');
            Route::get('nomenclador/valor/obtener', [PreciosValoresController::class, 'obtener'])->name('nomenclador.valor.obtener');
            
            Route::get('nomenclador/listas/creadas/{nom_padre}', [PreciosListasController::class, 'index'])->name('nomenclador.listas.listas');
            Route::get('nomenclador/listas/nuevo', [PreciosListasController::class, 'nuevo'])->name('nomenclador.listas.nuevo');
            Route::delete('nomenclador/listas/borrar/{id}', [PreciosListasController::class, 'borrar'])->name('nomenclador.listas.borrar');
            Route::get('nomenclador/listas/filtrar', [PreciosListasController::class, 'filtrar'])->name('nomenclador.listas.filtrar');
            Route::get('nomenclador/listas/modificar/{id}', [PreciosListasController::class, 'modificar'])->name('nomenclador.listas.modificar');
            Route::post('nomenclador/lista/guardar', [PreciosListasController::class, 'guardar'])->name('nomenclador.lista.guardar');

            Route::get("practicas_estudios/index/{nom_padre}", [NomPracticasEstudioController::class, 'index'])->name('nom_practicas_estudios.index');
            Route::get("practicas_estudios/create", [NomPracticasEstudioController::class, 'create'])->name('nom_practicas_estudios.create');
            Route::get("practicas_estudios/edit/{id}", [NomPracticasEstudioController::class, 'edit'])->name('nom_practicas_estudios.edit');
            Route::post('practicas_estudios/store', [NomPracticasEstudioController::class, 'store'])->name('nom_practicas_estudios.store');
            Route::delete('practicas_estudios/borrar/{id}', [NomPracticasEstudioController::class, 'destroy'])->name('nom_practicas_estudios.destroy');
            Route::get("practicas_estudios/values/{id}", [NomPracticasEstudioController::class, 'values'])->name('nom_practicas_estudios.values');
            Route::post('practicas_estudios/restore/{id}', [NomPracticasEstudioController::class, 'restore'])->name('nom_practicas_estudios.restore');
            

            // Route::get('consumos/partes', [ConsumoController::class, 'partes'])->name('consumos.partes');
            Route::get('consumos/partes/filtrar', [ConsumoController::class, 'parteFiltrar'])->name('consumos.partes.filtrar');
            Route::get('consumos/cargar/{id}', [ConsumoController::class, 'cargar'])->name('consumos.cargar');
            Route::post('consumos/valor/buscar', [ConsumoController::class, 'valorBuscar'])->name('consumos.valor.buscar');
            Route::post('consumos/guardar', [ConsumoController::class, 'guardar'])->name('consumos.guardar');
            Route::delete('consumos/borrar/{id}', [ConsumoController::class, 'destroy'])->name('consumos.borrar');
            Route::post('consumos/observar', [ConsumoController::class, 'observar'])->name('consumos.observar');

            Route::get('consumos/rendicion/filtrar', [ConsumoController::class, 'rendicionFiltrar'])->name('consumo.rendiciones.filtrar');
            Route::post('consumos/rendicion/guardar', [ConsumoController::class, 'rendicionStore'])->name('consumo.rendiciones.store');
            Route::get('consumos/rendicion/listado', [ConsumoController::class, 'rendicionListado'])->name('consumo.rendiciones.listado');
            Route::post('consumos/rendicion/listado/generar', [ConsumoController::class, 'rendicionListar'])->name('consumo.rendiciones.listar');
            Route::post('consumos/rendicion/estados', [ConsumoController::class, 'rendicionEstados'])->name('consumo.rendiciones.estados');
            Route::post('consumos/rendicion/revalorizar', [ConsumoController::class, 'rendicionRevalorizar'])->name('consumo.rendiciones.revalorizar');
            Route::post('consumos/rendicion/agregar', [ConsumoController::class, 'rendicionAgregar'])->name('consumo.rendiciones.agregar');
            Route::post('consumos/rendicion/agregarydif', [ConsumoController::class, 'agregarNuevoyDiferencia'])->name('consumo.rendiciones.agregarydiff');
        });

        Route::group(['middleware' => ['permission:adm_entidades']], function () {
            Route::resources(
                [
                    'profesionales' => ProfesionalController::class,
                    'centros' => CentroController::class,
                    'coberturas' => CoberturaController::class,
                    'parametros' => ParametroController::class,
                ]
            );
            Route::get('profesional/documentacion/{id}', [ProfesionalController::class, 'cargarDocum'])->name('profesional.cargar.documentacion');
            Route::post('profesional/documentacion/guardar', [ProfesionalController::class, 'guardarDocum'])->name('profesional.guardar.documentacion');
            Route::delete('profesional/documentacion/borrar/{id}', [ProfesionalController::class, 'borrarDocum'])->name('profesional.borrar.documentacion');
            Route::get('profesional/documentacion/download/{id}', [ProfesionalController::class, 'downloadDocum'])->name('profesional.download.documentacion');
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

        Route::group(['middleware' => ['permission:adm_presupuestos']], function () {
            Route::resource('presupuestos/cab', PresupuestoCabController::class)->names('presupuestos.cab');
            Route::get('presupuestos/payments', [PresupuestoCabController::class, 'payments'])->name('presupuestos.cab.payments');
            Route::resource('presupuestos/{id}/det', PresupuestoDetController::class)->names('presupuestos.det');
        });
    });
});
