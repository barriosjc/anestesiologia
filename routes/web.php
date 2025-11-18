<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Utiles\UtilController;
use App\Http\Controllers\cargas\ParteController;
use App\Http\Controllers\seguridad\RoleController;
use App\Http\Controllers\entidades\CentroController;
use App\Http\Controllers\seguridad\ProfileController;
use App\Http\Controllers\seguridad\UsuarioController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\entidades\NomPadreController;
use App\Http\Controllers\entidades\PacienteController;
use App\Http\Controllers\produccion\ConsumoController;
use App\Http\Controllers\seguridad\PermisosController;
use App\Http\Controllers\entidades\CoberturaController;
use App\Http\Controllers\entidades\ParametroController;
use App\Http\Controllers\entidades\NomencladorController;
use App\Http\Controllers\entidades\ProfesionalController;
use App\Http\Controllers\entidades\PreciosListasController;
use App\Http\Controllers\entidades\PreciosValoresController;
use App\Http\Controllers\entidades\PresupuestoCabController;
use App\Http\Controllers\entidades\PresupuestoDetController;
use App\Http\Controllers\entidades\PresupuestoPagosController;
use App\Http\Controllers\entidades\NomPracticasEstudioController;
use App\Http\Controllers\entidades\GerenciadoraCoberturaPadreController;

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
            
            Route::post('nomenclador/buscar', [NomencladorController::class, 'buscarCodDesc'])->name('nomenclador.buscar.coddesc');
            Route::get('nomenclador/lista/{nom_padre}', [NomencladorController::class, 'index'])->name('nomenclador.index');
            Route::get('nomenclador/create/{nom_padre}', [NomencladorController::class, 'create'])->name('nomenclador.create');
            Route::post('nomenclador/restore/{id}', [NomencladorController::class, 'restore'])->name('nomenclador.restore');
            Route::delete('nomenclador/destroy/{id}', [NomencladorController::class, 'destroy'])->name('nomenclador.destroy');
            Route::get('nomenclador/edit/{id}', [NomencladorController::class, 'edit'])->name('nomenclador.edit');
            Route::post('nomenclador/store', [NomencladorController::class, 'store'])->name('nomenclador.store');

            Route::get('nomenclador/valores/listas', [PreciosValoresController::class, 'index'])->name('nomenclador.valores.listas');
            Route::get('nomenclador/valores/filtrar', [PreciosValoresController::class, 'filtrar'])->name('nomenclador.valores.filtrar');
            Route::post('nomenclador/valores/grupo/nuevo', [PreciosValoresController::class, 'nuevoGrupo'])->name('nomenclador.valores.grupo.nuevo');
            Route::post('nomenclador/valores/guardar', [PreciosValoresController::class, 'guardarGrupo'])->name('nomenclador.valores.grupo.guardar');
            Route::delete('nomenclador/valor/borrar/{id}', [PreciosValoresController::class, 'borrar'])->name('nomenclador.valor.borrar');
            Route::get('nomenclador/valor/nuevo', [PreciosValoresController::class, 'nuevo'])->name('nomenclador.valor.nuevo');
            Route::post('nomenclador/valor/modificar', [PreciosValoresController::class, 'modificar'])->name('nomenclador.valor.modificar');
            Route::post('nomenclador/valor/guardar', [PreciosValoresController::class, 'guardar'])->name('nomenclador.valor.guardar');
            Route::post('nomenclador/valor/precio/guardar', [PreciosValoresController::class, 'valorGuardar'])->name('nomenclador.valor.precio.guardar');
            Route::post('nomenclador/valores/traer/uno', [PreciosValoresController::class, 'traerUno'])->name('nomenclador.valores.traer.uno');
            
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
            // Rutas expandidas de profesionales
            Route::get('profesionales', [ProfesionalController::class, 'index'])->name('profesionales.index');
            Route::get('profesionales/create', [ProfesionalController::class, 'create'])->name('profesionales.create');
            Route::post('profesionales', [ProfesionalController::class, 'store'])->name('profesionales.store');
            Route::get('profesionales/{profesionale}', [ProfesionalController::class, 'show'])->name('profesionales.show');
            Route::get('profesionales/{profesionale}/edit', [ProfesionalController::class, 'edit'])->name('profesionales.edit');
            Route::match(['put', 'patch'], 'profesionales/{profesionale}', [ProfesionalController::class, 'update'])->name('profesionales.update');
            Route::delete('profesionales/{profesionale}', [ProfesionalController::class, 'destroy'])->name('profesionales.destroy');

            // Rutas expandidas de centros
            Route::get('centros', [CentroController::class, 'index'])->name('centros.index');
            Route::get('centros/create', [CentroController::class, 'create'])->name('centros.create');
            Route::post('centros', [CentroController::class, 'store'])->name('centros.store');
            Route::get('centros/{centro}', [CentroController::class, 'show'])->name('centros.show');
            Route::get('centros/{centro}/edit', [CentroController::class, 'edit'])->name('centros.edit');
            Route::match(['put', 'patch'], 'centros/{centro}', [CentroController::class, 'update'])->name('centros.update');
            Route::delete('centros/{centro}', [CentroController::class, 'destroy'])->name('centros.destroy');

            // Rutas expandidas de coberturas
            Route::get('coberturas', [CoberturaController::class, 'index'])->name('coberturas.index');
            Route::get('coberturas/create', [CoberturaController::class, 'create'])->name('coberturas.create');
            Route::post('coberturas', [CoberturaController::class, 'store'])->name('coberturas.store');
            Route::get('coberturas/{cobertura}', [CoberturaController::class, 'show'])->name('coberturas.show');
            Route::get('coberturas/{cobertura}/edit', [CoberturaController::class, 'edit'])->name('coberturas.edit');
            Route::match(['put', 'patch'], 'coberturas/{cobertura}', [CoberturaController::class, 'update'])->name('coberturas.update');
            Route::delete('coberturas/{cobertura}', [CoberturaController::class, 'destroy'])->name('coberturas.destroy');
            Route::get('coberturas/buscar/datos', [CoberturaController::class, 'buscar'])->name('coberturas.buscar');

            // Rutas expandidas de parametros
            Route::get('parametros', [ParametroController::class, 'index'])->name('parametros.index');
            Route::get('parametros/create', [ParametroController::class, 'create'])->name('parametros.create');
            Route::post('parametros', [ParametroController::class, 'store'])->name('parametros.store');
            Route::get('parametros/{parametro}', [ParametroController::class, 'show'])->name('parametros.show');
            Route::get('parametros/{parametro}/edit', [ParametroController::class, 'edit'])->name('parametros.edit');
            Route::match(['put', 'patch'], 'parametros/{parametro}', [ParametroController::class, 'update'])->name('parametros.update');
            Route::delete('parametros/{parametro}', [ParametroController::class, 'destroy'])->name('parametros.destroy');

            // Rutas expandidas de gerenciadora_cobertura_padre
            Route::get('gerenciadora_cobertura_padre', [GerenciadoraCoberturaPadreController::class, 'index'])->name('gerenciadora_cobertura_padre.index');
            Route::get('gerenciadora_cobertura_padre/create', [GerenciadoraCoberturaPadreController::class, 'create'])->name('gerenciadora_cobertura_padre.create');
            Route::post('gerenciadora_cobertura_padre', [GerenciadoraCoberturaPadreController::class, 'store'])->name('gerenciadora_cobertura_padre.store');
            Route::get('gerenciadora_cobertura_padre/{gerenciadora_cobertura_padre}', [GerenciadoraCoberturaPadreController::class, 'show'])->name('gerenciadora_cobertura_padre.show');
            Route::get('gerenciadora_cobertura_padre/{gerenciadora_cobertura_padre}/edit', [GerenciadoraCoberturaPadreController::class, 'edit'])->name('gerenciadora_cobertura_padre.edit');
            Route::match(['put', 'patch'], 'gerenciadora_cobertura_padre/{gerenciadora_cobertura_padre}', [GerenciadoraCoberturaPadreController::class, 'update'])->name('gerenciadora_cobertura_padre.update');
            Route::delete('gerenciadora_cobertura_padre/{gerenciadora_cobertura_padre}', [GerenciadoraCoberturaPadreController::class, 'destroy'])->name('gerenciadora_cobertura_padre.destroy');

            Route::get('profesional/documentacion/{id}', [ProfesionalController::class, 'cargarDocum'])->name('profesional.cargar.documentacion');
            Route::post('profesional/documentacion/guardar', [ProfesionalController::class, 'guardarDocum'])->name('profesional.guardar.documentacion');
            Route::delete('profesional/documentacion/borrar/{id}', [ProfesionalController::class, 'borrarDocum'])->name('profesional.borrar.documentacion');
            Route::get('profesional/documentacion/download/{id}', [ProfesionalController::class, 'downloadDocum'])->name('profesional.download.documentacion');
        });
        
        Route::group(['middleware' => ['permission:adm_permisos']], function () {
            // Rutas expandidas de usuario
            Route::get('usuario', [UsuarioController::class, 'index'])->name('usuario.index');
            Route::get('usuario/create', [UsuarioController::class, 'create'])->name('usuario.create');
            Route::post('usuario', [UsuarioController::class, 'store'])->name('usuario.store');
            Route::get('usuario/{usuario}', [UsuarioController::class, 'show'])->name('usuario.show');
            Route::get('usuario/{usuario}/edit', [UsuarioController::class, 'edit'])->name('usuario.edit');
            Route::match(['put', 'patch'], 'usuario/{usuario}', [UsuarioController::class, 'update'])
    		->name('usuario.update');
            Route::delete('usuario/{usuario}', [UsuarioController::class, 'destroy'])->name('usuario.destroy');

            // Rutas expandidas de roles
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
            Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::match(['put', 'patch'], 'roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

            // Rutas expandidas de permisos
            Route::get('permisos', [PermisosController::class, 'index'])->name('permisos.index');
            Route::get('permisos/create', [PermisosController::class, 'create'])->name('permisos.create');
            Route::post('permisos', [PermisosController::class, 'store'])->name('permisos.store');
            Route::get('permisos/{permiso}', [PermisosController::class, 'show'])->name('permisos.show');
            Route::get('permisos/{permiso}/edit', [PermisosController::class, 'edit'])->name('permisos.edit');
            Route::match(['put', 'patch'], 'permisos/{permiso}', [PermisosController::class, 'update'])->name('permisos.update');
            Route::delete('permisos/{permiso}', [PermisosController::class, 'destroy'])->name('permisos.destroy');

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
            Route::get('presupuestos/cab', [PresupuestoCabController::class, 'index'])->name('presupuestos.cab.index');
            Route::get('presupuestos/cab/create', [PresupuestoCabController::class, 'create'])->name('presupuestos.cab.create');
            Route::post('presupuestos/cab', [PresupuestoCabController::class, 'store'])->name('presupuestos.cab.store');
            Route::get('presupuestos/cab/{cab}', [PresupuestoCabController::class, 'show'])->name('presupuestos.cab.show');
            Route::get('presupuestos/cab/{cab}/edit', [PresupuestoCabController::class, 'edit'])->name('presupuestos.cab.edit');
            Route::match(['put', 'patch'], 'presupuestos/cab/{cab}', [PresupuestoCabController::class, 'update'])->name('presupuestos.cab.update');
            Route::delete('presupuestos/cab/{cab}', [PresupuestoCabController::class, 'destroy'])->name('presupuestos.cab.destroy');
            Route::get('presupuestos/cab/{id}/print', [PresupuestoCabController::class, 'print'])->name('presupuestos.cab.print');
            Route::get('presupuestos/cab/{id}/partes', [PresupuestoCabController::class, 'partes'])->name('presupuestos.cab.partes');
            Route::get('presupuestos/cab/{id}/pagado', [PresupuestoCabController::class, 'pagado'])->name('presupuestos.cab.pagado');
            Route::get('presupuestos/cab/filtrar', [PresupuestoCabController::class, 'filtrar'])->name('presupuestos.cab.filtrar');

            // Rutas expandidas de presupuestos det
            Route::get('presupuestos/{id}/det', [PresupuestoDetController::class, 'index'])->name('presupuestos.det.index');
            Route::get('presupuestos/{id}/det/create', [PresupuestoDetController::class, 'create'])->name('presupuestos.det.create');
            Route::post('presupuestos/{id}/det', [PresupuestoDetController::class, 'store'])->name('presupuestos.det.store');
            Route::get('presupuestos/{id}/det/{det}', [PresupuestoDetController::class, 'show'])->name('presupuestos.det.show');
            Route::get('presupuestos/{id}/det/{det}/edit', [PresupuestoDetController::class, 'edit'])->name('presupuestos.det.edit');
            Route::match(['put', 'patch'], 'presupuestos/{id}/det/{det}', [PresupuestoDetController::class, 'update'])->name('presupuestos.det.update');
            Route::delete('presupuestos/{id}/det/{det}', [PresupuestoDetController::class, 'destroy'])->name('presupuestos.det.destroy');

            // Rutas expandidas de presupuestos pagos
            Route::get('presupuestos/{id}/pagos', [PresupuestoPagosController::class, 'index'])->name('presupuestos.pagos.index');
            Route::get('presupuestos/{id}/pagos/create', [PresupuestoPagosController::class, 'create'])->name('presupuestos.pagos.create');
            Route::post('presupuestos/{id}/pagos', [PresupuestoPagosController::class, 'store'])->name('presupuestos.pagos.store');
            Route::get('presupuestos/{id}/pagos/{pago}', [PresupuestoPagosController::class, 'show'])->name('presupuestos.pagos.show');
            Route::get('presupuestos/{id}/pagos/{pago}/edit', [PresupuestoPagosController::class, 'edit'])->name('presupuestos.pagos.edit');
            Route::match(['put', 'patch'], 'presupuestos/{id}/pagos/{pago}', [PresupuestoPagosController::class, 'update'])->name('presupuestos.pagos.update');
            Route::delete('presupuestos/{id}/pagos/{pago}', [PresupuestoPagosController::class, 'destroy'])->name('presupuestos.pagos.destroy');
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