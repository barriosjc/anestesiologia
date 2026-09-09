<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\cargas\ParteController;
use App\Http\Controllers\entidades\PresupuestoCabController;
use App\Http\Controllers\entidades\ProfesionalController;
use App\Http\Controllers\Utiles\UtilController;
use App\Livewire\Partes\ParteCreate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Auth::routes();
Route::get('login', \App\Livewire\Auth\Login::class)->name('login')->middleware('guest');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('login/restablecer', \App\Livewire\Auth\ResetPassword::class)->name('login.restablecer')->middleware('guest');


Route::group(['middleware' => 'auth'], function () {
    // Route::get('/run-migration', [UtilController::class, 'runMigrationAndSeeder'])
    //             ->middleware(['role:super-admin']);
    Route::get('password/profile', \App\Livewire\Perfil\PerfilPassword::class)->name('profile.password');

    Route::middleware('IngresoInicialMiddleware')->group(function () {
        $guard = 'web';

        Route::get('/', function () {
            return view('varios.novedades');
        })->name('main');

        //perfil de usuario
        Route::get('profile/{id}/editar', \App\Livewire\Perfil\PerfilIndex::class)->name('profile');

        Route::group(['middleware' => ['permission:adm_partes']], function () {
            Route::get('partes/calendar', \App\Livewire\Cargas\CalendarIndex::class)->name('partes_cab.calendar');

            Route::get('partes/create', ParteCreate::class)->name('partes_cab.create');
            Route::get('partes/edit/{id}', \App\Livewire\Partes\ParteCreate::class)->name('partes_cab.edit');
            Route::get('partes/filtrar', \App\Livewire\Partes\ParteIndex::class)->name('partes_cab.filtrar');

            Route::get('partes/det/create/{id}', \App\Livewire\Partes\ParteDetalle::class)->name('partes_det.create');
            Route::get('partes/det/download/{id}', [ParteController::class, 'download'])->name('partes_det.download');
        });

        Route::group(['middleware' => ['permission:adm_consumos']], function () {
            Route::get('nomencladores/listar', \App\Livewire\Nomencladores\NomPadreIndex::class)->name('nom_padres.index');

            Route::get('nomenclador/lista/{nom_padre}', \App\Livewire\Nomencladores\NomencladorIndex::class)->name('nomenclador.index');

            Route::get('nomenclador/valores/listas', \App\Livewire\Valores\ValoresIndex::class)->name('nomenclador.valores.listas');
            Route::get('nomenclador/valores/filtrar', function (Request $request) {
                return redirect()->route('nomenclador.valores.listas', $request->query());
            })->name('nomenclador.valores.filtrar');
            //se cambio a post porque borra y restaura
            Route::get('nomenclador/listas/creadas/{nom_padre}', \App\Livewire\Listas\AgrupadorListaIndex::class)->name('nomenclador.listas.listas');

            Route::get("practicas_estudios/index/{nom_padre}", \App\Livewire\Nomencladores\NomPracticaEstudioIndex::class)->name('nom_practicas_estudios.index');

            Route::get('consumos/partes/filtrar', \App\Livewire\Consumos\Partes\ConsumoPartesIndex::class)->name('consumos.partes.filtrar');
            Route::get('consumos/cargar/{id}', \App\Livewire\Consumos\Cargar\ConsumoCargarIndex::class)->name('consumos.cargar');

            Route::get('consumos/rendicion/filtrar', \App\Livewire\Consumos\Rendiciones\RendicionesIndex::class)->name('consumo.rendiciones.filtrar');
            Route::get('consumos/rendicion/listado', \App\Livewire\Reportes\ReportesForm::class)->name('consumo.rendiciones.listado');
            Route::get('consumos/rendicion/listado/generar', [\App\Http\Controllers\produccion\ReporteDescargaController::class, 'stream'])->name('reportes.stream');
        });

        Route::group(['middleware' => ['permission:adm_entidades']], function () {
            Route::get('profesionales', \App\Livewire\Entidades\ProfesionalIndex::class)->name('profesionales.index');
            Route::get('centros', \App\Livewire\Entidades\CentroIndex::class)->name('centros.index');
            Route::get('coberturas', \App\Livewire\Entidades\CoberturaIndex::class)->name('coberturas.index');
            Route::get('parametros', \App\Livewire\Entidades\ParametroIndex::class)->name('parametros.index');
            Route::get('gerenciadora_cobertura_padre', \App\Livewire\Entidades\GerenciadoraCoberturaPadreIndex::class)->name('gerenciadora_cobertura_padre.index');

            Route::get('profesional/documentacion/{id}', \App\Livewire\Entidades\ProfesionalDocumentacion::class)->name('profesional.cargar.documentacion');
            Route::get('profesional/documentacion/download/{id}', [ProfesionalController::class, 'downloadDocum'])->name('profesional.download.documentacion');
        });
        
        Route::group(['middleware' => ['permission:adm_permisos']], function () {
            Route::get('usuario', \App\Livewire\Seguridad\UsuarioIndex::class)->name('usuario.index');
            Route::get('roles', \App\Livewire\Seguridad\RoleIndex::class)->name('roles.index');
            Route::get('permisos', \App\Livewire\Seguridad\PermisoIndex::class)->name('permisos.index');

            // Rutas de asignación (componentes Livewire)
            Route::get('usuario/{id}/roles', \App\Livewire\Seguridad\UsuarioRoles::class)->name('usuarios.grupos');
            Route::get('usuario/{id}/permisos', \App\Livewire\Seguridad\UsuarioPermisos::class)->name('usuario.permisos');

            Route::get('roles/{id}/usuarios', \App\Livewire\Seguridad\RoleUsuarios::class)->name('roles.usuarios');
            Route::get('roles/{id}/permisos', \App\Livewire\Seguridad\RolePermisos::class)->name('roles.permisos');

            Route::get('permisos/{id}/usuarios', \App\Livewire\Seguridad\PermisoUsuarios::class)->name('permisos.usuarios');
            Route::get('permisos/{id}/roles', \App\Livewire\Seguridad\PermisoRoles::class)->name('permisos.grupos');
        });

        Route::group(['middleware' => ['permission:adm_presupuestos']], function () {
            // Rutas expandidas de presupuestos cab
            Route::get('presupuestos/cab', \App\Livewire\Presupuestos\PresupuestoIndex::class)->name('presupuestos.cab.index');
            Route::get('presupuestos/cab/create', \App\Livewire\Presupuestos\PresupuestoCreate::class)->name('presupuestos.cab.create');
            Route::get('presupuestos/cab/{id}/edit', \App\Livewire\Presupuestos\PresupuestoCreate::class)->name('presupuestos.cab.edit');
            Route::get('presupuestos/cab/{id}/print', [PresupuestoCabController::class, 'print'])->name('presupuestos.cab.print');

            // Rutas expandidas de presupuestos det
            Route::get('presupuestos/{id}/det/create', \App\Livewire\Presupuestos\PresupuestoDetIndex::class)->name('presupuestos.det.create');

            // Rutas expandidas de presupuestos pagos
            Route::get('presupuestos/{id}/pagos/create', \App\Livewire\Presupuestos\PresupuestoPagoIndex::class)->name('presupuestos.pagos.create');
        });
    });
});

// Ruta para verificar routes
// Route::get('/check-routes', function () {
//     return collect(\Illuminate\Support\Facades\Route::getRoutes())
//         ->pluck('action.as')
//         ->filter()
//         ->values();
// });

// Limpiar cache
// Route::get('/limpiar-cache', function () {
//     Artisan::call('optimize:clear');
//     return 'Cach� limpiada correctamente ?';
// });