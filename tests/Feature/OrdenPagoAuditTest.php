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

        // numero de OP tal como se guarda ahora (ver NumeroOrdenPagoExtractor)
        $orden = OrdenPagoAuditCab::create([
            'nro_op'         => '000000077740',
            'profesional_id' => $profesional->id,
            'fecha'          => '2026-03-02',
            'op_total'       => 100,
        ]);

        $this->assertSame('77740', $orden->nro_op_8);

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(\App\Livewire\OrdenesPagosAudit\OrdenPagoAuditIndex::class)
            ->assertSee('OP-0001')
            ->assertSee('Dr. Juan Perez')
            ->assertSee('OP 77740');

        // El modal de subida usa la ruta propia (XHR directo), no wire:model.
        $this->actingAs($this->usuarioConRol('super-admin'))
            ->get(route('ordenes_pagos_audit.cab'))
            ->assertOk()
            ->assertSee('data-upload-url="'.route('ordenes_pagos_audit.subir').'"', false)
            ->assertSee('upload-directo.js', false);
    }

    public function test_la_vista_muestra_las_retenciones_y_el_a_pagar(): void
    {
        $profesional = Profesional::create(['nombre' => 'Dr. Juan Perez', 'cuit' => '20111222333']);

        // Retenciones informadas por el archivo: caja 100, gastos adm. 320, IB 50.
        OrdenPagoAuditCab::create([
            'nro_op'           => '000000077740',
            'profesional_id'   => $profesional->id,
            'fecha'            => '2026-03-02',
            'op_total'         => 1000,
            'retencion_caja'   => 100,
            'retencion_gastos' => 320,
            'op_retenciones'   => 50,
        ]);

        // Orden sin retenciones en el archivo: no hay dato, se muestra "—".
        OrdenPagoAuditCab::create([
            'nro_op'         => '000000077741',
            'profesional_id' => $profesional->id,
            'fecha'          => '2026-03-03',
            'op_total'       => 500,
        ]);

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(\App\Livewire\OrdenesPagosAudit\OrdenPagoAuditIndex::class)
            ->assertSee('$ 1.000,00')
            ->assertSee('$ 100,00')
            ->assertSee('$ 320,00')
            ->assertSee('$ 50,00')
            // A pagar = 1000 - 50 - 100 - 320
            ->assertSee('$ 530,00')
            ->assertSee('$ 500,00')
            ->assertSee('—');
    }

    public function test_importa_un_archivo_csv(): void
    {
        $contenido = implode("\n", [
            'nro_op;profesional_id;fecha;op_total;op_retenciones',
            'OP-1001;1;01/03/2026;150000,50;25000,10',
            'OP-1002;1;02/03/2026;1000;100',
        ]);

        $respuesta = $this->actingAs($this->usuarioConRol('facturista'))
            ->post(route('ordenes_pagos_audit.subir'), [
                'archivo' => UploadedFile::fake()->createWithContent('ordenes.csv', $contenido),
            ]);

        $respuesta->assertOk()->assertJson(['ok' => true]);

        $this->assertSame(2, OrdenPagoAuditCab::count());
        $this->assertDatabaseHas('ordenes_pagos_audit_cab', [
            'nro_op'         => 'OP-1001',
            'op_total'       => 150000.50,
            'op_retenciones' => 25000.10,
        ]);
    }

    public function test_importa_un_archivo_csv_con_el_cuit_con_guiones(): void
    {
        // La base guarda el CUIT sin guiones; el archivo lo trae con guiones.
        $profesional = Profesional::create(['nombre' => 'Dr. Juan Perez', 'cuit' => '20111222333']);

        $contenido = implode("\n", [
            'nro_op;cuit;fecha;op_total;op_retenciones',
            'OP-3001;20-11122233-3;01/03/2026;150000,50;25000,10',
        ]);

        $this->actingAs($this->usuarioConRol('facturista'))
            ->post(route('ordenes_pagos_audit.subir'), [
                'archivo' => UploadedFile::fake()->createWithContent('ordenes.csv', $contenido),
            ])
            ->assertOk();

        $this->assertDatabaseHas('ordenes_pagos_audit_cab', [
            'nro_op'         => 'OP-3001',
            'profesional_id' => $profesional->id,
        ]);
    }

    public function test_importa_un_archivo_csv_con_el_cuit_de_la_base_con_guiones(): void
    {
        // La base guarda el CUIT con guiones; el archivo lo trae sin guiones.
        $profesional = Profesional::create(['nombre' => 'Dr. Juan Perez', 'cuit' => '20-11122233-3']);

        $contenido = implode("\n", [
            'nro_op;cuit;fecha;op_total;op_retenciones',
            'OP-3002;20111222333;01/03/2026;150000,50;25000,10',
        ]);

        $this->actingAs($this->usuarioConRol('facturista'))
            ->post(route('ordenes_pagos_audit.subir'), [
                'archivo' => UploadedFile::fake()->createWithContent('ordenes.csv', $contenido),
            ])
            ->assertOk();

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

        $respuesta = $this->actingAs($this->usuarioConRol('facturista'))
            ->post(route('ordenes_pagos_audit.subir'), ['archivo' => $archivo]);

        $respuesta->assertOk()->assertJson(['ok' => true]);

        $documento = DocumentoLlamaparse::firstOrFail();

        $respuesta->assertJson(['redirect' => route('documentos_llamaparse.ver', $documento)]);

        $this->assertSame('job-audit-1', $documento->job_id);
        $this->assertSame(EstadoDocumentoLlamaparse::Procesando, $documento->estado);
        $this->assertSame(0, OrdenPagoAuditCab::count());
    }

    public function test_no_importa_un_archivo_invalido(): void
    {
        $archivo = UploadedFile::fake()->createWithContent('ordenes.doc', 'contenido');

        $this->actingAs($this->usuarioConRol('facturista'))
            ->post(route('ordenes_pagos_audit.subir'), ['archivo' => $archivo])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['archivo']);

        $this->assertSame(0, OrdenPagoAuditCab::count());
    }

    public function test_la_subida_requiere_un_rol_autorizado(): void
    {
        $contenido = "nro_op;profesional_id;fecha;op_total;op_retenciones\nOP-4001;1;01/03/2026;100;10";

        $this->actingAs($this->usuarioConRol('otro-perfil'))
            ->post(route('ordenes_pagos_audit.subir'), [
                'archivo' => UploadedFile::fake()->createWithContent('ordenes.csv', $contenido),
            ])
            ->assertForbidden();

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
