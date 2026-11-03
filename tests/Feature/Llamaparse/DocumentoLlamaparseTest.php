<?php

namespace Tests\Feature\Llamaparse;

use App\Enums\EstadoDocumentoLlamaparse;
use App\Livewire\DocumentosLlamaparse\DocumentoLlamaparseDetalle;
use App\Livewire\DocumentosLlamaparse\DocumentoLlamaparseIndex;
use App\Models\DocumentoLlamaparse;
use App\Models\OrdenPagoAuditCab;
use App\Models\User;
use App\Services\LlamaParse\LlamaParseService;
use App\Services\LlamaParse\XlsxJsonService;
use App\Services\OrdenPagoAuditCompareService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentoLlamaparseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('llamaparse.api_key', 'llx-test');
        config()->set('llamaparse.base_url', 'https://api.cloud.llamaindex.ai/api/v2');
        config()->set('llamaparse.disk', 'local');

        Role::findOrCreate('super-admin', 'web');
        Role::findOrCreate('otro-perfil', 'web');

        foreach ([
            'adm_consumos',
            'adm_entidades',
            'adm_partes',
            'adm_presupuestos',
            'adm_permisos',
            'adm_calendario',
        ] as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        if (! Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function test_solo_usuarios_autorizados_acceden(): void
    {
        $this->actingAs($this->usuarioConRol('super-admin'))
            ->get(route('documentos_llamaparse.index'))
            ->assertOk();

        $this->actingAs($this->usuarioConRol('otro-perfil'))
            ->get(route('documentos_llamaparse.index'))
            ->assertForbidden();
    }

    public function test_sube_un_pdf_e_inicia_el_trabajo(): void
    {
        Storage::fake('local');

        Http::fake([
            'api.cloud.llamaindex.ai/api/v2/parse/upload' => Http::response([
                'id' => 'job-1',
                'status' => 'PENDING',
            ]),
        ]);

        $archivo = UploadedFile::fake()->createWithContent('orden.pdf', '%PDF-1.4 test');

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(DocumentoLlamaparseIndex::class)
            ->set('archivo', $archivo)
            ->call('subir')
            ->assertHasNoErrors();

        $documento = DocumentoLlamaparse::firstOrFail();

        $this->assertSame('job-1', $documento->job_id);
        $this->assertSame(EstadoDocumentoLlamaparse::Procesando, $documento->estado);
        Storage::disk('local')->assertExists($documento->archivo);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/parse/upload'));
    }

    public function test_consulta_un_trabajo_completado_descarga_xlsx_y_genera_json(): void
    {
        Storage::fake('local');

        $rutaFixture = $this->crearXlsx();
        $contenidoXlsx = (string) file_get_contents($rutaFixture);
        Storage::disk('local')->put('llamaparse/test/documento.pdf', 'pdf');

        $documento = DocumentoLlamaparse::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => null,
            'archivo' => 'llamaparse/test/documento.pdf',
            'nombre_original' => 'orden.pdf',
            'mime' => 'application/pdf',
            'tamano' => 10,
            'estado' => EstadoDocumentoLlamaparse::Procesando,
            'job_id' => 'job-1',
            'tier' => 'agentic',
            'progreso' => 15,
        ]);

        Http::fake([
            'api.cloud.llamaindex.ai/api/v2/parse/job-1*' => Http::response([
                'job' => [
                    'id' => 'job-1',
                    'status' => 'COMPLETED',
                    'usage' => ['credits' => 10],
                ],
                'markdown' => [
                    'pages' => [
                        ['page_number' => 1, 'success' => true, 'markdown' => '# Orden'],
                    ],
                ],
                'items' => [
                    'pages' => [
                        ['page_number' => 1, 'success' => true, 'items' => []],
                    ],
                ],
                'result_content_metadata' => [
                    'xlsx' => [
                        'exists' => true,
                        'presigned_url' => 'https://s3.test/resultado.xlsx',
                    ],
                ],
            ]),
            's3.test/resultado.xlsx' => Http::response($contenidoXlsx, 200),
        ]);

        app(LlamaParseService::class)->consultar($documento);

        $documento->refresh();

        $this->assertSame(EstadoDocumentoLlamaparse::Procesado, $documento->estado);
        $this->assertSame('llamaparse/'.$documento->uuid.'/resultado.xlsx', $documento->xlsx_archivo);
        Storage::disk('local')->assertExists($documento->xlsx_archivo);
        $this->assertNotNull($documento->json);
        $this->assertCount(1, $documento->json['atenciones']);
        $this->assertTrue($documento->json['resumen']['total_ok']);
        $this->assertSame('1180759', $documento->json['atenciones'][0]['atencion']);
        $this->assertSame('10.00', (string) $documento->usage_credits);
        $this->assertNotNull($documento->markdown);
        $this->assertNotNull($documento->items);
    }

    public function test_marca_error_si_la_api_rechaza_el_pdf(): void
    {
        Storage::fake('local');

        Http::fake([
            'api.cloud.llamaindex.ai/api/v2/parse/upload' => Http::response([
                'detail' => [['msg' => 'PDF invalido']],
            ], 422),
        ]);

        $archivo = UploadedFile::fake()->createWithContent('orden.pdf', '%PDF-1.4 test');

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(DocumentoLlamaparseIndex::class)
            ->set('archivo', $archivo)
            ->call('subir')
            ->assertHasErrors(['archivo']);

        $documento = DocumentoLlamaparse::firstOrFail();

        $this->assertSame(EstadoDocumentoLlamaparse::Error, $documento->estado);
        $this->assertStringContainsString('PDF invalido', (string) $documento->error);
    }

    public function test_un_timeout_al_consultar_mantiene_el_documento_en_proceso(): void
    {
        Storage::fake('local');

        $documento = DocumentoLlamaparse::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => null,
            'archivo' => 'llamaparse/test/documento.pdf',
            'nombre_original' => 'orden.pdf',
            'mime' => 'application/pdf',
            'tamano' => 10,
            'estado' => EstadoDocumentoLlamaparse::Procesando,
            'job_id' => 'job-1',
            'tier' => 'agentic',
            'progreso' => 15,
        ]);

        Http::fake(function () {
            throw new ConnectionException('cURL error 28: SSL connection timeout');
        });

        app(LlamaParseService::class)->consultar($documento);

        $documento->refresh();

        $this->assertSame(EstadoDocumentoLlamaparse::Procesando, $documento->estado);
        $this->assertNull($documento->error);
        $this->assertStringContainsString('reintentando', strtolower((string) $documento->progreso_mensaje));
    }

    public function test_reintentar_consulta_recupera_un_documento_con_error(): void
    {
        Storage::fake('local');

        $contenidoXlsx = (string) file_get_contents($this->crearXlsx());

        $documento = DocumentoLlamaparse::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => null,
            'archivo' => 'llamaparse/test/documento.pdf',
            'nombre_original' => 'orden.pdf',
            'mime' => 'application/pdf',
            'tamano' => 10,
            'estado' => EstadoDocumentoLlamaparse::Error,
            'job_id' => 'job-1',
            'tier' => 'agentic',
            'progreso' => 15,
            'error' => 'cURL error 28: SSL connection timeout',
        ]);

        Http::fake([
            'api.cloud.llamaindex.ai/api/v2/parse/job-1*' => Http::response([
                'job' => [
                    'id' => 'job-1',
                    'status' => 'COMPLETED',
                    'usage' => ['credits' => 10],
                ],
                'result_content_metadata' => [
                    'xlsx' => [
                        'exists' => true,
                        'presigned_url' => 'https://s3.test/resultado.xlsx',
                    ],
                ],
            ]),
            's3.test/resultado.xlsx' => Http::response($contenidoXlsx, 200),
        ]);

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(DocumentoLlamaparseDetalle::class, ['documento' => $documento])
            ->assertSee('Reintentar consulta')
            ->call('reintentarConsulta')
            ->assertHasNoErrors();

        $documento->refresh();

        $this->assertSame(EstadoDocumentoLlamaparse::Procesado, $documento->estado);
        $this->assertNotNull($documento->json);
        $this->assertNull($documento->error);
    }

    public function test_muestra_el_boton_auditar_pdf_cuando_el_json_esta_listo(): void
    {
        Storage::fake('local');

        $documento = $this->documentoProcesado();

        $this->actingAs($this->usuarioConRol('super-admin'))
            ->get(route('documentos_llamaparse.ver', $documento))
            ->assertOk()
            ->assertSee('JSON listo')
            ->assertSee('Ver relaciones')
            ->assertSee('Auditar PDF');
    }

    public function test_audita_desde_el_detalle_del_documento_y_redirige_al_resultado(): void
    {
        Storage::fake('local');

        $documento = $this->documentoProcesado();

        $this->mock(OrdenPagoAuditCompareService::class, function ($mock) {
            $mock->shouldReceive('auditar')->once()->andReturn([
                'success' => true,
                'message' => 'Auditoria generada correctamente.',
                'cabecera_id' => 99,
                'errors' => [],
                'inconsistencias' => [],
            ]);
        });

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(DocumentoLlamaparseDetalle::class, ['documento' => $documento])
            ->call('auditar')
            ->assertRedirect(route('ordenes_pagos_audit.detalle', ['orden' => 99]));
    }

    public function test_muestra_los_datos_inconsistentes_al_auditar_desde_el_detalle(): void
    {
        Storage::fake('local');

        $documento = $this->documentoProcesado();

        $this->mock(OrdenPagoAuditCompareService::class, function ($mock) {
            $mock->shouldReceive('auditar')->once()->andReturn([
                'success' => false,
                'message' => 'Se canceló la auditoría: hay 1 dato del archivo sin código para relacionar.',
                'cabecera_id' => null,
                'errors' => ['Atención 9999999 · sin código: La atención no existe en partes_cab para el profesional.'],
                'inconsistencias' => [[
                    'atencion' => '9999999',
                    'obra_social' => '143 - UOM',
                    'codigo' => null,
                    'descripcion' => null,
                    'porcentaje' => null,
                    'motivo' => 'La atención no existe en partes_cab para el profesional.',
                    'tipo' => 'atencion',
                ]],
            ]);
        });

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(DocumentoLlamaparseDetalle::class, ['documento' => $documento])
            ->call('auditar')
            ->assertSee('Auditoría cancelada')
            ->assertSee('La atención no existe en partes_cab')
            ->assertSet('inconsistenciasAuditoria', fn ($items) => count($items) === 1);
    }

    public function test_ver_relaciones_genera_la_tabla_sin_validacion_estricta(): void
    {
        Storage::fake('local');

        $documento = $this->documentoProcesado();

        $this->mock(OrdenPagoAuditCompareService::class, function ($mock) {
            $mock->shouldReceive('auditar')
                ->once()
                ->withArgs(fn ($documento, $estricto = true) => $estricto === false)
                ->andReturn([
                    'success' => true,
                    'message' => 'Auditoria generada correctamente.',
                    'cabecera_id' => 77,
                    'errors' => [],
                    'inconsistencias' => [],
                ]);
        });

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(DocumentoLlamaparseDetalle::class, ['documento' => $documento])
            ->call('verRelaciones')
            ->assertRedirect(route('ordenes_pagos_audit.detalle', ['orden' => 77]));
    }

    public function test_ver_relaciones_abre_la_auditoria_existente_sin_recalcular(): void
    {
        Storage::fake('local');

        $documento = $this->documentoProcesado();

        // La tabla ordenes_pagos_audit_cab tiene FK a profesionales, que en
        // esta suite no se crea porque la auditoria se mockea.
        if (! Schema::hasTable('profesionales')) {
            Schema::create('profesionales', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre')->nullable();
            });
        }

        $orden = OrdenPagoAuditCab::create([
            'documento_llamaparse_id' => $documento->id,
            'nro_op'                  => 'OP-REL',
        ]);

        $this->mock(OrdenPagoAuditCompareService::class, function ($mock) {
            $mock->shouldNotReceive('auditar');
        });

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(DocumentoLlamaparseDetalle::class, ['documento' => $documento])
            ->call('verRelaciones')
            ->assertRedirect(route('ordenes_pagos_audit.detalle', ['orden' => $orden->id]));
    }

    public function test_el_detalle_muestra_el_json_y_el_control_de_total(): void
    {
        Storage::fake('local');

        $documento = $this->documentoProcesado();

        $this->actingAs($this->usuarioConRol('super-admin'))
            ->get(route('documentos_llamaparse.ver', $documento))
            ->assertOk()
            ->assertSee('1180759')
            ->assertSee('Total coincide');

        $this->actingAs($this->usuarioConRol('otro-perfil'))
            ->get(route('documentos_llamaparse.ver', $documento))
            ->assertForbidden();
    }

    private function documentoProcesado(): DocumentoLlamaparse
    {
        $rutaXlsx = 'llamaparse/test/resultado.xlsx';
        $rutaPdf = 'llamaparse/test/documento.pdf';

        Storage::disk('local')->put($rutaXlsx, (string) file_get_contents($this->crearXlsx()));
        Storage::disk('local')->put($rutaPdf, 'pdf');

        $service = app(XlsxJsonService::class);
        $path = Storage::disk('local')->path($rutaXlsx);

        return DocumentoLlamaparse::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => null,
            'archivo' => $rutaPdf,
            'nombre_original' => 'orden.pdf',
            'mime' => 'application/pdf',
            'tamano' => 10,
            'estado' => EstadoDocumentoLlamaparse::Procesado,
            'job_id' => 'job-1',
            'tier' => 'agentic',
            'progreso' => 100,
            'xlsx_archivo' => $rutaXlsx,
            'json' => $service->documento($path, 'orden.pdf'),
            'json_crudo' => $service->procesar($path, 'orden.pdf'),
            'usage_credits' => 10,
            'procesado_at' => now(),
        ]);
    }

    private function crearXlsx(): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 1 - A');
        $hoja->fromArray([
            ['Comprobante', 'Fecha', 'Nº Liq.', 'Observaciones', 'Neto', 'IVA', 'Total', 'Cancela', 'Saldo'],
            ['LIQ L 0000-00100000', '15/09/2026', '888', 'TEST', '1,000.00', '0.00', '1,000.00', '1,000.00', '0.00'],
            [null, null, null, null, null, null, null, '1,000.00'],
        ]);

        $hoja = $spreadsheet->createSheet();
        $hoja->setTitle('Page 2 - A');
        $hoja->fromArray([
            ['Atención', 'Fecha', 'Paciente', 'Obra Social', 'Periodo', 'Tipo Nom.', 'Código', 'Descripción', 'Valor liq.'],
            ['1180759', '11/06/2026', 'SANCHEZ, CARLOS', '143 - UOM', '202606', 'ANESTESIA', '600011', 'Especial F', '1,000.00'],
        ]);

        $ruta = tempnam(sys_get_temp_dir(), 'llamaparse_test_');
        (new Xlsx($spreadsheet))->save($ruta);

        return $ruta;
    }

    private function usuarioConRol(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->assignRole($rol);

        return $usuario->fresh();
    }
}
