<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\cargas\ParteController;
use App\Http\Controllers\entidades\PresupuestoCabController;
use App\Http\Controllers\entidades\ProfesionalController;
use App\Http\Controllers\produccion\ConsumoController;
use App\Http\Controllers\seguridad\PermisosController;
use App\Http\Controllers\seguridad\ProfileController;
use App\Http\Controllers\seguridad\RoleController;
use App\Http\Controllers\seguridad\UsuarioController;
use App\Http\Controllers\Utiles\UtilController;
use App\Livewire\Partes\ParteCreate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Auth::routes();
Route::match(['get'], 'login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('login/restablecer', [ResetPasswordController::class, 'restablecer'])->name('login.restablecer');
Route::post('login/email', [ResetPasswordController::class, 'email'])->name('login.email');


Route::group(['middleware' => 'auth'], function () {
    Route::get('/run-migration', [UtilController::class, 'runMigrationAndSeeder'])
                ->middleware(['role:super-admin']);
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

        Route::group(['middleware' => ['permission:adm_partes']], function () {
            Route::get('partes/calendar', \App\Livewire\Cargas\CalendarIndex::class)->name('partes_cab.calendar');

            Route::get('partes/create', ParteCreate::class)->name('partes_cab.create');
            Route::get('partes/edit/{id}', \App\Livewire\Partes\ParteCreate::class)->name('partes_cab.edit');
            Route::get('partes/filtrar', \App\Livewire\Partes\ParteIndex::class)->name('partes_cab.filtrar');

            Route::get('partes/det/create/{id}', \App\Livewire\Partes\ParteDetalle::class)->name('partes_det.create');
            Route::get('partes/det/download/{id}', [ParteController::class, 'download'])->name('partes_det.download');
            
            Route::post('consumos/procesar', [ConsumoController::class, 'aProcesar'])->name('consumos.aprocesar');
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
            Route::post('consumos/observar', [ConsumoController::class, 'observar'])->name('consumos.observar');

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

            Route::get('profesional/documentacion/{id}', [ProfesionalController::class, 'cargarDocum'])->name('profesional.cargar.documentacion');
            Route::post('profesional/documentacion/guardar', [ProfesionalController::class, 'guardarDocum'])->name('profesional.guardar.documentacion');
            Route::delete('profesional/documentacion/borrar/{id}', [ProfesionalController::class, 'borrarDocum'])->name('profesional.borrar.documentacion');
            Route::get('profesional/documentacion/download/{id}', [ProfesionalController::class, 'downloadDocum'])->name('profesional.download.documentacion');
        });
        
        Route::group(['middleware' => ['permission:adm_permisos']], function () {
            Route::get('usuario', \App\Livewire\Seguridad\UsuarioIndex::class)->name('usuario.index');
            Route::get('roles', \App\Livewire\Seguridad\RoleIndex::class)->name('roles.index');
            Route::get('permisos', \App\Livewire\Seguridad\PermisoIndex::class)->name('permisos.index');

            // Rutas de asignación (se mantienen con los controladores)
            Route::get('usuario/{id}/roles/{rolid}/{tarea}', [UsuarioController::class, 'roles']);
            Route::get('usuario/{id}/roles', [UsuarioController::class, 'roles'])->name('usuarios.grupos');
            Route::get('usuario/{id}/permisos/{perid}/{tarea}', [UsuarioController::class, 'permisos']);
            Route::get('usuario/{id}/permisos', [UsuarioController::class, 'permisos']);

            Route::get('roles/{id}/permisos/{perid}/{tarea}', [RoleController::class, 'permisos']);
            Route::get('roles/{id}/permisos', [RoleController::class, 'permisos']);
            Route::get('roles/{id}/usuarios/{usuid}/{tarea}', [RoleController::class, 'usuarios']);
            Route::get('roles/{id}/usuarios', [RoleController::class, 'usuarios']);

            Route::get('permisos/{id}/usuarios/{usuid}/{tarea}', [PermisosController::class, 'usuarios']);
            Route::get('permisos/{id}/usuarios', [PermisosController::class, 'usuarios'])->name('permisos.usuarios');
            Route::get('permisos/{id}/roles/{rolid}/{tarea}', [PermisosController::class, 'roles']);
            Route::get('permisos/{id}/roles', [PermisosController::class, 'roles'])->name('permisos.grupos');
        });

        Route::group(['middleware' => ['permission:adm_presupuestos']], function () {
            // Rutas expandidas de presupuestos cab
            Route::get('presupuestos/cab', \App\Livewire\Presupuestos\PresupuestoIndex::class)->name('presupuestos.cab.index');
            Route::get('presupuestos/cab/create', \App\Livewire\Presupuestos\PresupuestoCreate::class)->name('presupuestos.cab.create');
            Route::get('presupuestos/cab/{id}/edit', \App\Livewire\Presupuestos\PresupuestoCreate::class)->name('presupuestos.cab.edit');
            Route::get('presupuestos/cab/{id}/print', [PresupuestoCabController::class, 'print'])->name('presupuestos.cab.print');
            Route::get('presupuestos/cab/{id}/partes', [PresupuestoCabController::class, 'partes'])->name('presupuestos.cab.partes');

            // Rutas expandidas de presupuestos det
            Route::get('presupuestos/{id}/det/create', \App\Livewire\Presupuestos\PresupuestoDetIndex::class)->name('presupuestos.det.create');

            // Rutas expandidas de presupuestos pagos
            Route::get('presupuestos/{id}/pagos/create', \App\Livewire\Presupuestos\PresupuestoPagoIndex::class)->name('presupuestos.pagos.create');
        });
    });
});

// Ruta para verificar routes
Route::get('/check-routes', function () {
    return collect(\Illuminate\Support\Facades\Route::getRoutes())
        ->pluck('action.as')
        ->filter()
        ->values();
});

// Limpiar cache
Route::get('/limpiar-cache', function () {
    Artisan::call('optimize:clear');
    return 'Cach� limpiada correctamente ?';
});