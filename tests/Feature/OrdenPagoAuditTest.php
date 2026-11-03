<?php

namespace Tests\Feature;

use App\Enums\EstadoDocumentoLlamaparse;
use App\Models\DocumentoLlamaparse;
use App\Models\OrdenPagoAuditCab;
use App\Models\Profesional;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrdenPagoAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('llamaparse.api_key', 'llx-test');
        config()->set('llamaparse.base_url', 'https://api.cloud.llamaindex.ai/api/v2');

        Role::findOrCreate('super-admin', 'web');
        Role::findOrCreate('facturista', 'web');
        Role::findOrCreate('otro-perfil', 'web');

        // El sidebar consulta estos permisos: si no existen, spatie lanza la excepcion.
        Permission::findOrCreate('adm_consumos', 'web');
        Permission::findOrCreate('adm_entidades', 'web');
        Permission::findOrCreate('adm_partes', 'web');
        Permission::findOrCreate('adm_presupuestos', 'web');
        Permission::findOrCreate('adm_permisos', 'web');
        Permission::findOrCreate('adm_calendario', 'web');

        // La tabla "profesionales" es preexistente del sistema y no tiene migracion.
        if (! Schema::hasTable('profesionales')) {
            Schema::create('profesionales', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre');
                $table->string('email')->nullable();
                $table->string('cuit', 11)->nullable();
                $table->string('telefono')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function test_solo_super_admin_y_facturista_pueden_acceder(): void
    {
        $this->actingAs($this->usuarioConRol('super-admin'))
            ->get(route('ordenes_pagos_audit.cab'))
            ->assertOk();

        $this->actingAs($this->usuarioConRol('facturista'))
            ->get(route('ordenes_pagos_audit.cab'))
            ->assertOk();

        $this->actingAs($this->usuarioConRol('otro-perfil'))
            ->get(route('ordenes_pagos_audit.cab'))
            ->assertForbidden();
    }

    public function test_la_vista_muestra_el_listado_de_ordenes_de_pago(): void
    {
        $profesional = Profesional::create(['nombre' => 'Dr. Juan Perez', 'cuit' => '20111222333']);

        OrdenPagoAuditCab::create([
            'nro_op'         => 'OP-0001',
            'profesional_id' => $profesional->id,
            'fecha'          => '2026-03-01',
            'op_total'       => 150000.50,
            'op_retenciones' => 25000.10,
        ]);

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(\App\Livewire\OrdenesPagosAudit\OrdenPagoAuditIndex::class)
            ->assertSee('OP-0001')
            ->assertSee('Dr. Juan Perez');
    }

    public function test_importa_un_archivo_csv(): void
    {
        Storage::fake('local');

        $contenido = implode("\n", [
            'nro_op;profesional_id;fecha;op_total;op_retenciones',
            'OP-1001;1;01/03/2026;150000,50;25000,10',
            'OP-1002;1;02/03/2026;1000;100',
        ]);

        $archivo = UploadedFile::fake()->createWithContent('ordenes.csv', $contenido);

        Livewire::actingAs($this->usuarioConRol('facturista'))
            ->test(\App\Livewire\OrdenesPagosAudit\OrdenPagoAuditIndex::class)
            ->set('archivo', $archivo)
            ->call('importar')
            ->assertHasNoErrors();

        $this->assertSame(2, OrdenPagoAuditCab::count());
        $this->assertDatabaseHas('ordenes_pagos_audit_cab', [
            'nro_op'         => 'OP-1001',
            'op_total'       => 150000.50,
            'op_retenciones' => 25000.10,
        ]);
    }

    public function test_importa_un_archivo_csv_con_el_cuit_con_guiones(): void
    {
        Storage::fake('local');

        // La base guarda el CUIT sin guiones; el archivo lo trae con guiones.
        $profesional = Profesional::create(['nombre' => 'Dr. Juan Perez', 'cuit' => '20111222333']);

        $contenido = implode("\n", [
            'nro_op;cuit;fecha;op_total;op_retenciones',
            'OP-3001;20-11122233-3;01/03/2026;150000,50;25000,10',
        ]);

        $archivo = UploadedFile::fake()->createWithContent('ordenes.csv', $contenido);

        Livewire::actingAs($this->usuarioConRol('facturista'))
            ->test(\App\Livewire\OrdenesPagosAudit\OrdenPagoAuditIndex::class)
            ->set('archivo', $archivo)
            ->call('importar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ordenes_pagos_audit_cab', [
            'nro_op'         => 'OP-3001',
            'profesional_id' => $profesional->id,
        ]);
    }

    public function test_importa_un_archivo_csv_con_el_cuit_de_la_base_con_guiones(): void
    {
        Storage::fake('local');

        // La base guarda el CUIT con guiones; el archivo lo trae sin guiones.
        $profesional = Profesional::create(['nombre' => 'Dr. Juan Perez', 'cuit' => '20-11122233-3']);

        $contenido = implode("\n", [
            'nro_op;cuit;fecha;op_total;op_retenciones',
            'OP-3002;20111222333;01/03/2026;150000,50;25000,10',
        ]);

        $archivo = UploadedFile::fake()->createWithContent('ordenes.csv', $contenido);

        Livewire::actingAs($this->usuarioConRol('facturista'))
            ->test(\App\Livewire\OrdenesPagosAudit\OrdenPagoAuditIndex::class)
            ->set('archivo', $archivo)
            ->call('importar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ordenes_pagos_audit_cab', [
            'nro_op'         => 'OP-3002',
            'profesional_id' => $profesional->id,
        ]);
    }

    public function test_un_pdf_se_procesa_con_llamaparse(): void
    {
        Storage::fake('local');

        Http::fake([
            'api.cloud.llamaindex.ai/api/v2/parse/upload' => Http::response([
                'id' => 'job-audit-1',
                'status' => 'PENDING',
            ]),
        ]);

        $archivo = UploadedFile::fake()->createWithContent('orden.pdf', '%PDF-1.4 test');

        Livewire::actingAs($this->usuarioConRol('facturista'))
            ->test(\App\Livewire\OrdenesPagosAudit\OrdenPagoAuditIndex::class)
            ->set('archivo', $archivo)
            ->call('importar')
            ->assertHasNoErrors();

        $documento = DocumentoLlamaparse::firstOrFail();

        $this->assertSame('job-audit-1', $documento->job_id);
        $this->assertSame(EstadoDocumentoLlamaparse::Procesando, $documento->estado);
        $this->assertSame(0, OrdenPagoAuditCab::count());
    }

    public function test_no_importa_un_archivo_invalido(): void
    {
        Storage::fake('local');

        $archivo = UploadedFile::fake()->createWithContent('ordenes.doc', 'contenido');

        Livewire::actingAs($this->usuarioConRol('facturista'))
            ->test(\App\Livewire\OrdenesPagosAudit\OrdenPagoAuditIndex::class)
            ->set('archivo', $archivo)
            ->call('importar')
            ->assertHasErrors(['archivo']);

        $this->assertSame(0, OrdenPagoAuditCab::count());
    }

    public function test_el_sidebar_muestra_el_enlace_para_super_admin_y_facturista(): void
    {
        $enlace = route('ordenes_pagos_audit.cab');

        $this->actingAs($this->usuarioConRol('super-admin'))
            ->get(route('ordenes_pagos_audit.cab'))
            ->assertSee('Ordenes Pagos Audit.')
            ->assertSee($enlace, false);

        $this->actingAs($this->usuarioConRol('facturista'))
            ->get(route('ordenes_pagos_audit.cab'))
            ->assertSee('Ordenes Pagos Audit.')
            ->assertSee($enlace, false);
    }

    public function test_el_sidebar_no_muestra_el_enlace_para_otros_perfiles(): void
    {
        $this->actingAs($this->usuarioConRol('otro-perfil'))
            ->get(route('consumo.rendiciones.listado'))
            ->assertDontSee('Ordenes Pagos Audit.');
    }

    public function test_puede_borrar_una_orden_de_pago(): void
    {
        $orden = OrdenPagoAuditCab::create([
            'nro_op'   => 'OP-2001',
            'fecha'    => '2026-03-01',
            'op_total' => 100,
        ]);

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(\App\Livewire\OrdenesPagosAudit\OrdenPagoAuditIndex::class)
            ->call('borrar', $orden->id);

        $this->assertDatabaseMissing('ordenes_pagos_audit_cab', ['id' => $orden->id]);
    }

    private function usuarioConRol(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->assignRole($rol);

        return $usuario->fresh();
    }
}
