<?php

namespace Tests\Feature;

use App\Livewire\OrdenesPagosAudit\OrdenPagoAuditDetalle;
use App\Models\Cobertura;
use App\Models\ConsumoCab;
use App\Models\ConsumoDet;
use App\Models\DocumentoLlamaparse;
use App\Models\Estado;
use App\Models\Nivel;
use App\Models\Nomenclador;
use App\Models\OrdenPagoAuditCab;
use App\Models\OrdenPagoAuditDet;
use App\Models\ParteCab;
use App\Models\Profesional;
use App\Models\User;
use App\Services\OrdenPagoAuditCompareService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrdenPagoAuditCompareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super-admin', 'web');
        Role::findOrCreate('facturista', 'web');

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

        $this->crearTablasPreexistentes();
    }

    public function test_compara_el_json_contra_la_base_y_guarda_las_diferencias(): void
    {
        $profesional = Profesional::create([
            'nombre' => 'PEREZ, JUAN',
            'cuit'   => '20111222333',
        ]);

        $cobertura = Cobertura::create([
            'nombre' => 'UOM',
            'sigla'  => 'UOM',
            'cuit'    => '30-12345678-9',
            'cod_uom' => '143',
        ]);

        $parte = ParteCab::create([
            'profesional_id' => $profesional->id,
            'atencion'       => '1180759',
            'fec_prestacion' => '2026-06-11 09:00:00',
        ]);
        $parte->cobertura_id = $cobertura->id;
        $parte->save();

        $consumoCab = new ConsumoCab();
        $consumoCab->parte_cab_id = $parte->id;
        $consumoCab->save();

        $this->consumo($consumoCab->id, '600011', 'Especial F', 500);
        $this->consumo($consumoCab->id, '600023', 'Evaluacion', 100);
        $this->consumo($consumoCab->id, '600004', 'Mayor F', 50);

        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);
        Nivel::create(['nivel' => '600023', 'cod_uom' => '600023', 'descripcion' => 'Evaluacion']);

        $documento = DocumentoLlamaparse::create([
            'uuid'            => '11111111-1111-1111-1111-111111111111',
            'archivo'         => 'documento.pdf',
            'nombre_original' => 'OP junio.pdf',
            'mime'            => 'application/pdf',
            'estado'          => 'procesado',
            'json'            => [
                'documento' => [
                    'tipo'            => 'detalle_liquidacion',
                    'numero'          => null,
                    'fecha'           => '11/06/2026',
                    'total_informado' => 700,
                ],
                'cabecera' => [
                    'profesional'         => 'PEREZ, JUAN',
                    'cuit'                => '20111222333',
                    'importe'             => 700,
                    'numero_liquidacion'  => 'L 0000-0001',
                ],
                'atenciones' => [
                    [
                        'atencion'     => '1180759',
                        'fecha'        => '11/06/2026',
                        'obra_social'  => '143 - UOM',
                        'detalles'     => [
                            [
                                'fecha'              => '11/06/2026',
                                'codigo_nivel'       => '600011',
                                'descripcion_nivel'  => 'Especial F',
                                'periodo'            => '202606',
                                'importe'            => 600,
                            ],
                            [
                                'fecha'              => '11/06/2026',
                                'codigo_nivel'       => '600023',
                                'descripcion_nivel'  => 'Evaluacion',
                                'periodo'            => '202606',
                                'importe'            => 100,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertTrue($resultado['success'], $resultado['message']);

        $orden = OrdenPagoAuditCab::firstOrFail();

        $this->assertSame($documento->id, $orden->documento_llamaparse_id);
        $this->assertSame($profesional->id, $orden->profesional_id);
        $this->assertSame('L 0000-0001', $orden->nro_liquidacion);
        $this->assertSame('2026-06-11', $orden->fecha->toDateString());
        $this->assertSame(700.0, (float) $orden->op_total);
        $this->assertSame(650.0, (float) $orden->total_sistema);
        $this->assertSame(50.0, (float) $orden->total_diferencia);
        $this->assertSame(2, $orden->cantidad_diferencias);
        $this->assertNotNull($orden->auditado_at);

        $detalle = OrdenPagoAuditDet::where('codigo_archivo', '600011')->firstOrFail();
        $this->assertSame(600.0, (float) $detalle->valor_archivo);
        $this->assertSame(500.0, (float) $detalle->valor_sistema);
        $this->assertSame(100.0, (float) $detalle->diferencia);
        $this->assertSame(20.0, (float) $detalle->porcentaje_diferencia);
        $this->assertSame('media', $detalle->severidad);
        $this->assertSame('diferencia', $detalle->estado);
        $this->assertFalse($detalle->dif_obra_social);
        $this->assertFalse($detalle->dif_fecha);

        $this->assertDatabaseHas('ordenes_pagos_audit_det', [
            'orden_pago_audit_cab_id' => $orden->id,
            'codigo_archivo'          => '600023',
            'estado'                  => 'ok',
            'severidad'               => 'sin-diferencia',
        ]);

        $this->assertDatabaseHas('ordenes_pagos_audit_det', [
            'orden_pago_audit_cab_id' => $orden->id,
            'nivel_sistema'           => '600004',
            'estado'                  => 'sin_archivo',
            'severidad'               => 'alta',
        ]);
    }

    public function test_la_vista_de_detalle_muestra_las_diferencias_y_permite_filtrar(): void
    {
        $profesional = Profesional::create([
            'nombre' => 'PEREZ, JUAN',
            'cuit'   => '20111222333',
        ]);

        $cobertura = Cobertura::create([
            'nombre' => 'UOM',
            'sigla'  => 'UOM',
            'cuit'    => '30-12345678-9',
            'cod_uom' => '143',
        ]);

        $parte = ParteCab::create([
            'profesional_id' => $profesional->id,
            'atencion'       => '1180759',
            'fec_prestacion' => '2026-06-11 09:00:00',
        ]);
        $parte->cobertura_id = $cobertura->id;
        $parte->save();

        $consumoCab = new ConsumoCab();
        $consumoCab->parte_cab_id = $parte->id;
        $consumoCab->save();

        $this->consumo($consumoCab->id, '600011', 'Especial F', 500);
        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);

        $documento = DocumentoLlamaparse::create([
            'uuid'            => '22222222-2222-2222-2222-222222222222',
            'archivo'         => 'documento.pdf',
            'nombre_original' => 'OP junio.pdf',
            'mime'            => 'application/pdf',
            'estado'          => 'procesado',
            'json'            => [
                'documento' => [
                    'tipo'            => 'detalle_liquidacion',
                    'fecha'           => '11/06/2026',
                    'total_informado' => 600,
                ],
                'cabecera' => [
                    'profesional' => 'PEREZ, JUAN',
                    'cuit'        => '20111222333',
                ],
                'atenciones' => [[
                    'atencion'    => '1180759',
                    'fecha'       => '11/06/2026',
                    'obra_social' => '143 - UOM',
                    'detalles'    => [[
                        'fecha'             => '11/06/2026',
                        'codigo_nivel'      => '600011',
                        'descripcion_nivel' => 'Especial F',
                        'importe'           => 600,
                    ]],
                ]],
            ],
        ]);

        app(OrdenPagoAuditCompareService::class)->auditar($documento);
        $orden = OrdenPagoAuditCab::firstOrFail();

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(OrdenPagoAuditDetalle::class, ['orden' => $orden])
            ->assertSee('Auditoría OP')
            ->assertSee('1180759')
            ->assertSee(route('consumo.rendiciones.filtrar', ['nro_parte' => $parte->id]), false)
            ->assertSee('600011')
            ->assertSee('Diferencia')
            ->set('soloDiferencias', true)
            ->assertSee('600011')
            ->set('filtroEstado', 'sin_nivel')
            ->assertDontSee('600011');
    }

    public function test_ordena_el_detalle_por_obra_social_y_atencion(): void
    {
        $orden = $this->crearAuditoriaConVariasObrasSociales();

        $component = Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(OrdenPagoAuditDetalle::class, ['orden' => $orden]);

        // Orden por obra social: numerico y, dentro de cada obra social, por
        // atención (16, 16, 78, 143 y no 143, 16, 78).
        $component->call('ordenarPor', 'obra_social');

        $this->assertSame(
            ['16', '16', '79', '143'],
            $component->viewData('detalles')->getCollection()->pluck('cod_uom_archivo')->all()
        );

        $component->call('ordenarPor', 'obra_social');

        $this->assertSame(
            ['143', '79', '16', '16'],
            $component->viewData('detalles')->getCollection()->pluck('cod_uom_archivo')->all()
        );

        // Orden por atención: y dentro de cada atención, por obra social.
        $component->call('ordenarPor', 'atencion');

        $this->assertSame(
            ['16', '143', '16', '79'],
            $component->viewData('detalles')->getCollection()->pluck('cod_uom_archivo')->all()
        );

        $component->call('ordenarPor', 'atencion');

        $this->assertSame(
            ['79', '16', '143', '16'],
            $component->viewData('detalles')->getCollection()->pluck('cod_uom_archivo')->all()
        );

        // Vuelve al orden por defecto (gravedad, atención y código).
        $component->call('ordenPorDefecto');

        $this->assertSame(
            ['16', '143', '16', '79'],
            $component->viewData('detalles')->getCollection()->pluck('cod_uom_archivo')->all()
        );
    }

    public function test_marca_las_filas_de_cada_atencion_con_un_color_distinto(): void
    {
        $orden = $this->crearAuditoriaConVariasObrasSociales();

        $component = Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(OrdenPagoAuditDetalle::class, ['orden' => $orden]);

        $colores = $component->viewData('colorGrupo');
        $porAtencion = [];

        foreach ($component->viewData('detalles')->getCollection() as $detalle) {
            $porAtencion[$detalle->atencion][] = $colores[$detalle->id];
        }

        // Las filas de la misma atención comparten el color.
        $this->assertCount(1, array_unique($porAtencion['1000001']));

        // Y los colores van alternando entre azul y naranja.
        $this->assertSame('azul', $porAtencion['1000001'][0]);
        $this->assertSame('naranja', $porAtencion['1000002'][0]);
        $this->assertSame('azul', $porAtencion['1000003'][0]);

        // El HTML usa la clase del color en la celda de atención.
        $this->assertStringContainsString(
            'opa-grupo-'.$porAtencion['1000001'][0],
            $component->html()
        );
    }

    public function test_las_filas_que_no_estan_en_el_archivo_heredan_la_obra_social_de_la_atencion(): void
    {
        $profesional = Profesional::create([
            'nombre' => 'PEREZ, JUAN',
            'cuit'   => '20111222333',
        ]);

        $cobertura = Cobertura::create([
            'nombre' => 'UOM',
            'sigla'  => 'UOM',
            'cuit'    => '30-12345678-9',
            'cod_uom' => '143',
        ]);

        $parte = ParteCab::create([
            'profesional_id' => $profesional->id,
            'atencion'       => '1180759',
            'fec_prestacion' => '2026-06-11 09:00:00',
        ]);
        $parte->cobertura_id = $cobertura->id;
        $parte->save();

        $consumoCab = new ConsumoCab();
        $consumoCab->parte_cab_id = $parte->id;
        $consumoCab->save();

        // En el sistema hay una practica 600011 que en el archivo no aparece.
        $this->consumo($consumoCab->id, '600011', 'Especial F', 100);

        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);
        Nivel::create(['nivel' => '600023', 'cod_uom' => '600023', 'descripcion' => 'Evaluacion']);

        $documento = DocumentoLlamaparse::create([
            'uuid'            => '20202020-2020-2020-2020-202020202020',
            'archivo'         => 'documento.pdf',
            'nombre_original' => 'OP junio.pdf',
            'mime'            => 'application/pdf',
            'estado'          => 'procesado',
            'json'            => [
                'documento' => [
                    'tipo'            => 'detalle_liquidacion',
                    'fecha'           => '11/06/2026',
                    'total_informado' => 100,
                ],
                'cabecera' => [
                    'profesional' => 'PEREZ, JUAN',
                    'cuit'        => '20111222333',
                ],
                'atenciones' => [[
                    'atencion'    => '1180759',
                    'fecha'       => '11/06/2026',
                    'obra_social' => '143 - UOM',
                    'detalles'    => [[
                        'fecha'             => '11/06/2026',
                        'codigo_nivel'      => '600023',
                        'descripcion_nivel' => 'Evaluacion',
                        'importe'           => 100,
                    ]],
                ]],
            ],
        ]);

        app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $fila = OrdenPagoAuditDet::where('estado', 'sin_archivo')->firstOrFail();

        // La fila que no esta en el archivo toma la obra social de su atención,
        // para que al ordenar no quede separada del resto de la atención.
        $this->assertSame('1180759', $fila->atencion);
        $this->assertSame('143', $fila->cod_uom_archivo);
        $this->assertSame('143 - UOM', $fila->obra_social_archivo);
    }

    public function test_muestra_cada_item_del_archivo_aunque_se_repita_en_la_atencion(): void
    {
        ['orden' => $orden, 'consumo' => $consumo] = $this->crearAuditoriaDeUnConsumo(
            [
                [
                    'codigo_nivel'      => '600011',
                    'descripcion_nivel' => 'Especial F',
                    'importe'           => 100,
                ],
                [
                    'codigo_nivel'      => '600011',
                    'descripcion_nivel' => 'Especial F',
                    'importe'           => 100,
                ],
            ],
            '31313131-3131-3131-3131-313131313131',
            200
        );

        $filas = OrdenPagoAuditDet::orderBy('id')->get();

        // El archivo repite el item dentro de la misma atencion: se muestran
        // las dos filas en lugar de sumarlas en una sola.
        $this->assertCount(2, $filas);

        // La primera compara contra el consumo del sistema.
        $this->assertSame('600011', $filas[0]->codigo_archivo);
        $this->assertSame(100.0, (float) $filas[0]->valor_archivo);
        $this->assertSame(100.0, (float) $filas[0]->valor_sistema);
        $this->assertSame(0.0, (float) $filas[0]->diferencia);
        $this->assertSame('ok', $filas[0]->estado);
        $this->assertSame($consumo->id, $filas[0]->consumo_det_id);

        // La repeticion queda sin contraparte en el sistema.
        $this->assertSame('600011', $filas[1]->codigo_archivo);
        $this->assertSame(100.0, (float) $filas[1]->valor_archivo);
        $this->assertSame(0.0, (float) $filas[1]->valor_sistema);
        $this->assertSame(100.0, (float) $filas[1]->diferencia);
        $this->assertSame('sin_correspondencia', $filas[1]->estado);
        $this->assertNull($filas[1]->consumo_det_id);

        // Los totales no cambian: el sistema sigue contando una sola vez.
        $this->assertSame(200.0, (float) $orden->op_total);
        $this->assertSame(100.0, (float) $orden->total_sistema);
        $this->assertSame(100.0, (float) $orden->total_diferencia);
        $this->assertSame(1, $orden->cantidad_diferencias);
    }

    public function test_la_descripcion_distinta_no_marca_la_fila_como_diferencia(): void
    {
        ['orden' => $orden] = $this->crearAuditoriaDeUnConsumo(
            [[
                'codigo_nivel'      => '600011',
                // El archivo trae la descripcion abreviada: el match es por
                // codigo, asi que no debe contar como diferencia.
                'descripcion_nivel' => 'Especial',
                'importe'           => 100,
            ]],
            '32323232-3232-3232-3232-323232323232'
        );

        $fila = OrdenPagoAuditDet::firstOrFail();

        $this->assertTrue($fila->dif_descripcion);
        $this->assertSame('ok', $fila->estado);
        $this->assertSame('sin-diferencia', $fila->severidad);
        $this->assertFalse($fila->dif_valor);
        $this->assertSame(0, $orden->fresh()->cantidad_diferencias);

        // La descripcion se marca con un punto rojo, sin pintar la fila.
        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(OrdenPagoAuditDetalle::class, ['orden' => $orden])
            ->assertSee('opa-dif-punto', false)
            ->assertSee('Descripción distinta', false)
            ->assertSee('Coincide');
    }

    public function test_toggle_all_marca_y_desmarca_las_filas_de_la_pantalla(): void
    {
        ['orden' => $orden] = $this->crearAuditoriaDeUnConsumo(
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]],
            '32323232-3232-3232-3232-323232323232'
        );

        $component = Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(OrdenPagoAuditDetalle::class, ['orden' => $orden]);

        $ids = $component->viewData('detalles')->getCollection()->pluck('id')->all();

        $component->call('toggleAll', true)
            ->assertSet('seleccionados', function ($seleccionados) use ($ids) {
                return array_map('intval', $seleccionados) === $ids;
            });

        $component->call('toggleAll', false)
            ->assertSet('seleccionados', []);
    }

    public function test_pasa_a_pagado_los_consumos_de_las_filas_seleccionadas(): void
    {
        ['orden' => $orden, 'consumo' => $consumo] = $this->crearAuditoriaDeUnConsumo(
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]],
            '33333333-3434-3434-3434-343434343434'
        );

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(OrdenPagoAuditDetalle::class, ['orden' => $orden])
            ->call('toggleAll', true)
            ->assertSet('seleccionados', fn ($seleccionados) => count($seleccionados) === 1)
            ->call('pasarAPagado')
            ->assertSet('mensajeTipo', 'success')
            ->assertSet('seleccionados', []);

        $this->assertSame(6, ConsumoDet::findOrFail($consumo->id)->estado_id);

        $fila = OrdenPagoAuditDet::firstOrFail();
        $parte = ParteCab::findOrFail($fila->parte_cab_id);
        $this->assertSame(6, $parte->fresh()->estado_id);
    }

    public function test_pasa_a_refacturar_con_el_periodo_elegido(): void
    {
        ['orden' => $orden, 'consumo' => $consumo] = $this->crearAuditoriaDeUnConsumo(
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]],
            '35353535-3535-3535-3535-353535353535'
        );

        $fila = OrdenPagoAuditDet::firstOrFail();

        $component = Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(OrdenPagoAuditDetalle::class, ['orden' => $orden])
            ->call('abrirRefacturar', $fila->id)
            ->assertSet('filaRefac', $fila->id)
            ->assertSet('periodoRefac', now()->format('Y/m'))
            ->assertDispatched('open-modal');

        $component->set('periodoRefac', '2026/09')
            ->call('cambiarEstadoRefacturar')
            ->assertSet('mensajeTipo', 'success');

        $consumo = ConsumoDet::findOrFail($consumo->id);

        $this->assertSame(7, $consumo->estado_id);
        $this->assertSame('2026/09', $consumo->periodo);

        // El parte asociado tambien queda en estado "A refacturar".
        $parte = ParteCab::findOrFail($fila->parte_cab_id);
        $this->assertSame(7, $parte->fresh()->estado_id);
    }

    public function test_refacturar_funciona_aunque_el_consumo_estuviera_en_otro_estado(): void
    {
        ['orden' => $orden, 'consumo' => $consumo] = $this->crearAuditoriaDeUnConsumo(
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]],
            '39393939-3939-3939-3939-393939393939'
        );

        // Estado previo que, con la validacion anterior, bloqueaba el pasaje
        // a refacturar (por ejemplo un consumo ya pagado).
        DB::table('consumos_det')->where('id', $consumo->id)->update(['estado_id' => 6]);

        $fila = OrdenPagoAuditDet::firstOrFail();
        $parte = ParteCab::findOrFail($fila->parte_cab_id);
        $parte->estado_id = 6;
        $parte->save();

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(OrdenPagoAuditDetalle::class, ['orden' => $orden])
            ->call('abrirRefacturar', $fila->id)
            ->set('periodoRefac', '2026/10')
            ->call('cambiarEstadoRefacturar')
            ->assertSet('mensajeTipo', 'success');

        $consumo = ConsumoDet::findOrFail($consumo->id);

        $this->assertSame(7, $consumo->estado_id);
        $this->assertSame('2026/10', $consumo->periodo);
        $this->assertSame(7, $parte->fresh()->estado_id);
    }

    public function test_avisa_cuando_la_fila_no_tiene_consumo_del_sistema(): void
    {
        $this->crearParteConConsumo('UOM', '143');

        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);
        Nivel::create(['nivel' => '600023', 'cod_uom' => '600023', 'descripcion' => 'Evaluacion']);

        // El archivo informa una practica que no esta cargada en el sistema.
        $documento = $this->documentoConDetalles(
            '36363636-3636-3636-3636-363636363636',
            '143 - UOM',
            [[
                'codigo_nivel'      => '600023',
                'descripcion_nivel' => 'Evaluacion',
                'importe'           => 100,
            ]]
        );

        app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $orden = OrdenPagoAuditCab::firstOrFail();
        $fila = OrdenPagoAuditDet::where('estado', 'sin_consumo')->firstOrFail();

        $component = Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(OrdenPagoAuditDetalle::class, ['orden' => $orden]);

        $component->set('seleccionados', [(string) $fila->id])
            ->call('pasarAPagado')
            ->assertSet('mensajeTipo', 'warning');

        $component->call('abrirRefacturar', $fila->id)
            ->assertSet('mensajeTipo', 'warning')
            ->assertNotDispatched('open-modal');

        // El consumo del sistema queda intacto.
        $this->assertSame(5, ConsumoDet::firstOrFail()->estado_id);
    }

    public function test_la_grilla_muestra_los_controles_de_acciones(): void
    {
        ['orden' => $orden] = $this->crearAuditoriaDeUnConsumo(
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]],
            '37373737-3737-3737-3737-373737373737'
        );

        Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(OrdenPagoAuditDetalle::class, ['orden' => $orden])
            ->assertSee('Cambiar a pagado')
            ->assertSee('Pasar pagado')
            ->assertSee('Pasar a refacturar')
            ->assertSee('confirmAction')
            ->assertSee('Acciones')
            ->assertSee('opaRefacturarModal');
    }

    public function test_muestra_el_estado_del_consumo_y_permite_cambiarlo(): void
    {
        DB::table('estados')->insert([
            ['id' => 3, 'descripcion' => 'A liquidar'],
            ['id' => 4, 'descripcion' => 'En facturación'],
        ]);

        ['orden' => $orden] = $this->crearAuditoriaDeUnConsumo(
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]],
            '38383838-3838-3838-3838-383838383838'
        );

        $fila = OrdenPagoAuditDet::firstOrFail();
        $parte = ParteCab::findOrFail($fila->parte_cab_id);
        $parte->estado_id = 3;
        $parte->save();

        // La pill muestra el estado del consumo, no el del parte.
        $consumo = ConsumoDet::findOrFail($fila->consumo_det_id);
        $consumo->estado_id = 4;
        $consumo->save();

        $component = Livewire::actingAs($this->usuarioConRol('super-admin'))
            ->test(OrdenPagoAuditDetalle::class, ['orden' => $orden]);

        $component->assertSee('En facturación')
            ->call('abrirEstadoModal', $fila->id)
            ->assertDispatched('open-modal')
            ->assertSet('estado_cambio_id', $parte->id)
            ->assertSet('filaEstado', $fila->id)
            ->set('estado_cambio_estado', 3)
            ->call('cambiarEstado')
            ->assertDispatched('close-modal');

        $this->assertDatabaseHas('partes_cab', [
            'id'        => $parte->id,
            'estado_id' => 3,
        ]);

        $this->assertDatabaseHas('consumos_det', [
            'id'        => $consumo->id,
            'estado_id' => 3,
        ]);
    }

    /**
     * Auditoria con tres atenciones y una de ellas repetida con otra obra
     * social, para probar los cortes por atención.
     */
    private function crearAuditoriaConVariasObrasSociales(): OrdenPagoAuditCab
    {
        $profesional = Profesional::create([
            'nombre' => 'PEREZ, JUAN',
            'cuit'   => '20111222333',
        ]);

        // Las atenciones se crean en un orden que diferencia el orden por
        // atención del orden numerico por obra social.
        $this->crearParteParaObraSocial($profesional, '1000001', '143');
        $this->crearParteParaObraSocial($profesional, '1000002', '16');
        $this->crearParteParaObraSocial($profesional, '1000003', '79');

        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);

        $documento = DocumentoLlamaparse::create([
            'uuid'            => '19191919-1919-1919-1919-191919191919',
            'archivo'         => 'documento.pdf',
            'nombre_original' => 'OP junio.pdf',
            'mime'            => 'application/pdf',
            'estado'          => 'procesado',
            'json'            => [
                'documento' => [
                    'tipo'            => 'detalle_liquidacion',
                    'fecha'           => '11/06/2026',
                    'total_informado' => 400,
                ],
                'cabecera' => [
                    'profesional' => 'PEREZ, JUAN',
                    'cuit'        => '20111222333',
                ],
                'atenciones' => [
                    $this->atencionArchivo('1000001', '143 - UOM'),
                    // La misma atención con otra obra social: permite ver que
                    // dentro de una atención las filas se ordenan por obra social.
                    $this->atencionArchivo('1000001', '16 - OBRASOC'),
                    $this->atencionArchivo('1000002', '16 - OBRASOC'),
                    $this->atencionArchivo('1000003', '79 - OBRASOC'),
                ],
            ],
        ]);

        app(OrdenPagoAuditCompareService::class)->auditar($documento);

        return OrdenPagoAuditCab::firstOrFail();
    }

    public function test_resuelve_el_nivel_por_descripcion_cuando_informa_porcentaje(): void
    {
        $profesional = Profesional::create([
            'nombre' => 'PEREZ, JUAN',
            'cuit'   => '20111222333',
        ]);

        $cobertura = Cobertura::create([
            'nombre' => 'UOM',
            'sigla'  => 'UOM',
            'cuit'    => '30-12345678-9',
            'cod_uom' => '143',
        ]);

        $parte = ParteCab::create([
            'profesional_id' => $profesional->id,
            'atencion'       => '1180759',
            'fec_prestacion' => '2026-06-11 09:00:00',
        ]);
        $parte->cobertura_id = $cobertura->id;
        $parte->save();

        $consumoCab = new ConsumoCab();
        $consumoCab->parte_cab_id = $parte->id;
        $consumoCab->save();

        $nomenclador = Nomenclador::create([
            'nom_padre_id' => 1,
            'codigo'       => '600011',
            'nivel'        => 'EF',
            'descripcion'  => 'Especial F',
        ]);

        $this->consumoDetalle($consumoCab->id, $nomenclador->id, 500, 100);
        $this->consumoDetalle($consumoCab->id, $nomenclador->id, 150, 30);

        Nivel::create(['nivel' => 'EF', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);
        Nivel::create(['nivel' => 'EV', 'cod_uom' => '600023', 'descripcion' => 'Evaluacion']);

        $documento = DocumentoLlamaparse::create([
            'uuid'            => '33333333-3333-3333-3333-333333333333',
            'archivo'         => 'documento.pdf',
            'nombre_original' => 'OP junio.pdf',
            'mime'            => 'application/pdf',
            'estado'          => 'procesado',
            'json'            => [
                'documento' => [
                    'tipo'            => 'detalle_liquidacion',
                    'fecha'           => '11/06/2026',
                    'total_informado' => 650,
                ],
                'cabecera' => [
                    'profesional' => 'PEREZ, JUAN',
                    'cuit'        => '20111222333',
                ],
                'atenciones' => [[
                    'atencion'    => '1180759',
                    'fecha'       => '11/06/2026',
                    'obra_social' => '143 - UOM',
                    'detalles'    => [
                        [
                            'fecha'             => '11/06/2026',
                            'codigo_nivel'      => '600023',
                            'descripcion_nivel' => 'Especial F 30%',
                            'importe'           => 150,
                        ],
                        [
                            'fecha'             => '11/06/2026',
                            'codigo_nivel'      => '600011',
                            'descripcion_nivel' => 'Especial F',
                            'importe'           => 500,
                        ],
                    ],
                ]],
            ],
        ]);

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertTrue($resultado['success'], $resultado['message']);

        $detalle30 = OrdenPagoAuditDet::where('codigo_archivo', '600023')->firstOrFail();
        $this->assertSame('EF', $detalle30->nivel_sistema);
        $this->assertSame(150.0, (float) $detalle30->valor_sistema);
        $this->assertSame(30.0, (float) $detalle30->porcentaje_archivo);
        $this->assertSame(30.0, (float) $detalle30->porcentaje_sistema);
        $this->assertSame('ok', $detalle30->estado);

        $detalle100 = OrdenPagoAuditDet::where('codigo_archivo', '600011')->firstOrFail();
        $this->assertSame(500.0, (float) $detalle100->valor_sistema);
        $this->assertSame(100.0, (float) $detalle100->porcentaje_sistema);
        $this->assertSame('ok', $detalle100->estado);

        $this->assertSame(650.0, (float) OrdenPagoAuditCab::firstOrFail()->total_sistema);
    }

    public function test_para_ioma_cruza_por_codigo_de_nomenclador_y_no_por_nivel(): void
    {
        $profesional = Profesional::create([
            'nombre' => 'PEREZ, JUAN',
            'cuit'   => '20111222333',
        ]);

        $cobertura = Cobertura::create([
            'nombre'  => 'IOMA',
            'sigla'   => 'IOMA',
            'cuit'    => '30-12345678-9',
            'cod_uom' => '78',
        ]);

        $parte = ParteCab::create([
            'profesional_id' => $profesional->id,
            'atencion'       => '1180759',
            'fec_prestacion' => '2026-06-11 09:00:00',
        ]);
        $parte->cobertura_id = $cobertura->id;
        $parte->save();

        $consumoCab = new ConsumoCab();
        $consumoCab->parte_cab_id = $parte->id;
        $consumoCab->save();

        Nivel::create(['nivel' => 'MB', 'cod_uom' => '600001', 'descripcion' => 'Nivel MB']);
        Nivel::create(['nivel' => 'MD', 'cod_uom' => '600002', 'descripcion' => 'Nivel MD']);

        $nomencladorUno = Nomenclador::create([
            'nom_padre_id' => 2,
            'codigo'       => '010101',
            'nivel'        => 'MB',
            'descripcion'  => 'Absceso - drenaje',
        ]);
        $nomencladorDos = Nomenclador::create([
            'nom_padre_id' => 2,
            'codigo'       => '010102',
            'nivel'        => 'MB',
            'descripcion'  => 'Herida hasta aponeurosis',
        ]);
        $nomencladorTres = Nomenclador::create([
            'nom_padre_id' => 2,
            'codigo'       => '010103',
            'nivel'        => 'MB',
            'descripcion'  => 'Cuerpos extraños',
        ]);

        $this->consumoDetalle($consumoCab->id, $nomencladorUno->id, 100, 100, 2);
        $this->consumoDetalle($consumoCab->id, $nomencladorDos->id, 200, 100, 2);
        $this->consumoDetalle($consumoCab->id, $nomencladorTres->id, 50, 100, 2);

        $documento = DocumentoLlamaparse::create([
            'uuid'            => '44444444-4444-4444-4444-444444444444',
            'archivo'         => 'documento.pdf',
            'nombre_original' => 'OP junio.pdf',
            'mime'            => 'application/pdf',
            'estado'          => 'procesado',
            'json'            => [
                'documento' => [
                    'tipo'            => 'detalle_liquidacion',
                    'fecha'           => '11/06/2026',
                    'total_informado' => 300,
                ],
                'cabecera' => [
                    'profesional' => 'PEREZ, JUAN',
                    'cuit'        => '20111222333',
                ],
                'atenciones' => [[
                    'atencion'    => '1180759',
                    'fecha'       => '11/06/2026',
                    'obra_social' => '78 - IOMA',
                    'detalles'    => [
                        [
                            'fecha'             => '11/06/2026',
                            'codigo_nivel'      => '010101',
                            'descripcion_nivel' => 'Absceso - drenaje',
                            'importe'           => 100,
                        ],
                        [
                            'fecha'             => '11/06/2026',
                            'codigo_nivel'      => '010102',
                            'descripcion_nivel' => 'Herida hasta aponeurosis',
                            'importe'           => 200,
                        ],
                    ],
                ]],
            ],
        ]);

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertTrue($resultado['success'], $resultado['message']);

        $detalleUno = OrdenPagoAuditDet::where('codigo_archivo', '010101')->firstOrFail();
        $this->assertSame('MB', $detalleUno->nivel_sistema);
        $this->assertSame('010101', $detalleUno->codigo_sistema);
        $this->assertSame(100.0, (float) $detalleUno->valor_sistema);
        $this->assertSame('ok', $detalleUno->estado);

        $detalleDos = OrdenPagoAuditDet::where('codigo_archivo', '010102')->firstOrFail();
        $this->assertSame('MB', $detalleDos->nivel_sistema);
        $this->assertSame('010102', $detalleDos->codigo_sistema);
        $this->assertSame(200.0, (float) $detalleDos->valor_sistema);
        $this->assertSame('ok', $detalleDos->estado);

        $this->assertDatabaseHas('ordenes_pagos_audit_det', [
            'orden_pago_audit_cab_id' => OrdenPagoAuditCab::firstOrFail()->id,
            'estado'                  => 'sin_archivo',
            'codigo_sistema'          => '010103',
        ]);

        $this->assertSame(350.0, (float) OrdenPagoAuditCab::firstOrFail()->total_sistema);
    }

    public function test_cancela_la_auditoria_si_un_codigo_no_ioma_no_tiene_equivalencia(): void
    {
        $this->crearParteConConsumo('UOM', '143');

        $documento = $this->documentoConDetalles(
            '55555555-5555-5555-5555-555555555555',
            '143 - UOM',
            [[
                'codigo_nivel'      => '999999',
                'descripcion_nivel' => 'Codigo sin equivalencia',
                'importe'           => 100,
            ]]
        );

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertFalse($resultado['success']);
        $this->assertNull($resultado['cabecera_id']);
        $this->assertCount(1, $resultado['inconsistencias']);
        $this->assertSame('codigo', $resultado['inconsistencias'][0]['tipo']);
        $this->assertSame('999999', $resultado['inconsistencias'][0]['codigo']);
        $this->assertStringContainsString('sin código para relacionar', $resultado['message']);

        $this->assertSame(0, OrdenPagoAuditCab::count());
        $this->assertSame(0, OrdenPagoAuditDet::count());
    }

    public function test_sin_validacion_estricta_genera_la_tabla_con_las_filas_sin_contraparte(): void
    {
        $this->crearParteConConsumo('UOM', '143');

        $documento = $this->documentoConDetalles(
            '56565656-5656-5656-5656-565656565656',
            '143 - UOM',
            [[
                'codigo_nivel'      => '999999',
                'descripcion_nivel' => 'Codigo sin equivalencia',
                'importe'           => 100,
            ]]
        );

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento, false);

        $this->assertTrue($resultado['success'], $resultado['message']);
        $this->assertNotNull($resultado['cabecera_id']);

        $orden = OrdenPagoAuditCab::firstOrFail();

        // La fila del archivo que no tiene equivalencia queda marcada como que
        // no esta en el sistema.
        $filaArchivo = OrdenPagoAuditDet::where('estado', 'sin_nivel')->firstOrFail();
        $this->assertSame('999999', $filaArchivo->codigo_archivo);
        $this->assertSame('No está en sistema (código)', $filaArchivo->estado_label);

        // La practica del sistema que no esta en el archivo tambien se muestra.
        $filaSistema = OrdenPagoAuditDet::where('estado', 'sin_archivo')->firstOrFail();
        $this->assertSame('600011', $filaSistema->nivel_sistema);
        $this->assertSame('Falta en el archivo', $filaSistema->estado_label);

        $this->assertSame(2, $orden->cantidad_diferencias);
    }

    public function test_cancela_la_auditoria_si_un_codigo_ioma_no_esta_en_el_nomenclador(): void
    {
        $this->crearParteConConsumo('IOMA', '78');

        $documento = $this->documentoConDetalles(
            '66666666-6666-6666-6666-666666666666',
            '78 - IOMA',
            [[
                'codigo_nivel'      => '099999',
                'descripcion_nivel' => 'Practica IOMA sin codigo',
                'importe'           => 100,
            ]]
        );

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertFalse($resultado['success']);
        $this->assertCount(1, $resultado['inconsistencias']);
        $this->assertSame('ioma', $resultado['inconsistencias'][0]['tipo']);
        $this->assertSame('099999', $resultado['inconsistencias'][0]['codigo']);

        $this->assertSame(0, OrdenPagoAuditCab::count());
        $this->assertSame(0, OrdenPagoAuditDet::count());
    }

    public function test_cancela_la_auditoria_si_una_descripcion_con_porcentaje_no_tiene_nivel(): void
    {
        $this->crearParteConConsumo('UOM', '143');

        // El codigo existe en niveles.cod_uom, pero con otra descripcion: para
        // las descripciones con porcentaje no se usa el codigo como respaldo.
        Nivel::create(['nivel' => 'EF', 'cod_uom' => '600011', 'descripcion' => 'Otra descripcion']);

        $documento = $this->documentoConDetalles(
            '77777777-7777-7777-7777-777777777777',
            '143 - UOM',
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F 30%',
                'importe'           => 100,
            ]]
        );

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertFalse($resultado['success']);
        $this->assertCount(1, $resultado['inconsistencias']);
        $this->assertSame('descripcion', $resultado['inconsistencias'][0]['tipo']);
        $this->assertSame(30.0, $resultado['inconsistencias'][0]['porcentaje']);

        $this->assertSame(0, OrdenPagoAuditCab::count());
        $this->assertSame(0, OrdenPagoAuditDet::count());
    }

    public function test_cancela_la_auditoria_si_la_atencion_no_existe_en_partes_cab(): void
    {
        $this->crearParteConConsumo('UOM', '143');

        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);

        $documento = $this->documentoConDetalles(
            '99999999-9999-9999-9999-999999999999',
            '143 - UOM',
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]],
            '9999999'
        );

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertFalse($resultado['success']);
        $this->assertCount(1, $resultado['inconsistencias']);
        $this->assertSame('atencion', $resultado['inconsistencias'][0]['tipo']);
        $this->assertSame('9999999', $resultado['inconsistencias'][0]['atencion']);
        $this->assertStringContainsString('partes_cab', $resultado['inconsistencias'][0]['motivo']);

        $this->assertSame(0, OrdenPagoAuditCab::count());
        $this->assertSame(0, OrdenPagoAuditDet::count());
    }

    public function test_cancela_la_auditoria_si_la_obra_social_no_cruza_con_coberturas(): void
    {
        $this->crearParteConConsumo('UOM', '143');

        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);

        $documento = $this->documentoConDetalles(
            '12121212-1212-1212-1212-121212121212',
            '555 - OTRA OBRA SOCIAL',
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]]
        );

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertFalse($resultado['success']);
        $this->assertCount(1, $resultado['inconsistencias']);
        $this->assertSame('obra_social', $resultado['inconsistencias'][0]['tipo']);
        $this->assertSame('555', $resultado['inconsistencias'][0]['codigo']);
        $this->assertStringContainsString('coberturas.cod_uom', $resultado['inconsistencias'][0]['motivo']);

        $this->assertSame(0, OrdenPagoAuditCab::count());
        $this->assertSame(0, OrdenPagoAuditDet::count());
    }

    public function test_identifica_el_profesional_comparando_el_cuit_del_pdf_sin_guiones(): void
    {
        $this->crearParteConConsumo('UOM', '143');
        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);

        $documento = $this->documentoConDetalles(
            '13131313-1313-1313-1313-131313131313',
            '143 - UOM',
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]]
        );

        $json = $documento->json;
        $json['cabecera']['cuit'] = '20-11122233-3';
        $documento->update(['json' => $json]);

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertTrue($resultado['success'], $resultado['message']);
        $this->assertSame(
            Profesional::firstOrFail()->id,
            OrdenPagoAuditCab::firstOrFail()->profesional_id
        );
    }

    public function test_identifica_el_profesional_cuando_la_base_tiene_el_cuit_con_guiones(): void
    {
        $this->crearParteConConsumo('UOM', '143');
        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);

        // La base guarda el CUIT con guiones; el PDF lo trae sin guiones.
        Profesional::firstOrFail()->update(['cuit' => '20-11122233-3']);

        $documento = $this->documentoConDetalles(
            '17171717-1717-1717-1717-171717171717',
            '143 - UOM',
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]]
        );

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertTrue($resultado['success'], $resultado['message']);
        $this->assertSame(
            Profesional::firstOrFail()->id,
            OrdenPagoAuditCab::firstOrFail()->profesional_id
        );
    }

    public function test_relaciona_la_atencion_cuando_la_base_la_guarda_con_ceros_a_la_izquierda(): void
    {
        $this->crearParteConConsumo('UOM', '143');
        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);

        // La base guarda la atención con ceros a la izquierda; el PDF no.
        ParteCab::firstOrFail()->update(['atencion' => '01180759']);

        $documento = $this->documentoConDetalles(
            '18181818-1818-1818-1818-181818181818',
            '143 - UOM',
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]]
        );

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertTrue($resultado['success'], $resultado['message']);
        $this->assertSame(
            ParteCab::firstOrFail()->id,
            OrdenPagoAuditDet::firstOrFail()->parte_cab_id
        );
    }

    public function test_identifica_el_profesional_por_nombre_aunque_la_base_tenga_acentos(): void
    {
        Profesional::create([
            'nombre' => 'PÉREZ, JUAN',
            'cuit'   => '27999999991',
        ]);

        $documento = $this->documentoConDetalles(
            '14141414-1414-1414-1414-141414141414',
            '143 - UOM',
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]]
        );

        $json = $documento->json;
        $json['cabecera']['cuit'] = '20-11111111-1';
        $json['cabecera']['profesional'] = 'PEREZ, JUAN';
        $documento->update(['json' => $json]);

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        // El profesional se resolvio por nombre; el error que sigue es por los
        // datos del archivo, no por el profesional.
        $this->assertStringNotContainsString(
            'No se pudo identificar el profesional',
            $resultado['message']
        );
        $this->assertStringContainsString('Se canceló la auditoría', $resultado['message']);
    }

    public function test_resuelve_el_profesional_con_el_cuit_que_esta_en_los_items_cuando_el_json_no_lo_trae(): void
    {
        $this->crearParteConConsumo('UOM', '143');
        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);

        $documento = $this->documentoConDetalles(
            '16161616-1616-1616-1616-161616161616',
            '143 - UOM',
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]]
        );

        $json = $documento->json;
        // El parser viejo tomo el encabezado "CUIT" como nombre y no guardo el CUIT.
        $json['cabecera']['profesional'] = 'CUIT';
        unset($json['cabecera']['cuit']);
        $documento->update([
            'json'  => $json,
            'items' => [[
                'items' => [[
                    'type' => 'table',
                    'md'   => "| **Agente** | **CUIT** |\n| PEREZ, JUAN | 20-11122233-3 |",
                ]],
            ]],
        ]);

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertTrue($resultado['success'], $resultado['message']);
        $this->assertSame(
            Profesional::firstOrFail()->id,
            OrdenPagoAuditCab::firstOrFail()->profesional_id
        );
    }

    public function test_informa_que_datos_del_profesional_no_encontro(): void
    {
        Profesional::create([
            'nombre' => 'PEREZ, MARIA',
            'cuit'   => '27123456789',
        ]);

        $documento = $this->documentoConDetalles(
            '15151515-1515-1515-1515-151515151515',
            '143 - UOM',
            [[
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]]
        );

        $json = $documento->json;
        $json['cabecera']['cuit'] = '20-99999999-9';
        $json['cabecera']['profesional'] = 'PEREZ, JUAN';
        $documento->update(['json' => $json]);

        $resultado = app(OrdenPagoAuditCompareService::class)->auditar($documento);

        $this->assertFalse($resultado['success']);
        $this->assertNull($resultado['cabecera_id']);
        $this->assertStringContainsString('CUIT del PDF: 20-99999999-9', $resultado['message']);
        $this->assertStringContainsString('Nombre del PDF: PEREZ, JUAN', $resultado['message']);

        $errores = implode(' | ', $resultado['errors']);

        $this->assertStringContainsString('CUIT: «20-99999999-9»', $errores);
        $this->assertStringContainsString('sin guiones: 20999999999', $errores);
        $this->assertStringContainsString('profesionales.cuit', $errores);
        $this->assertStringContainsString('Nombre: «PEREZ, JUAN»', $errores);
        $this->assertStringContainsString('profesionales.nombre', $errores);
        $this->assertStringContainsString('Posibles coincidencias', $errores);

        $this->assertSame(0, OrdenPagoAuditCab::count());
    }

    public function test_el_modal_muestra_los_datos_inconsistentes_y_no_guarda_nada(): void
    {
        $this->crearParteConConsumo('UOM', '143');

        $documento = $this->documentoConDetalles(
            '88888888-8888-8888-8888-888888888888',
            '143 - UOM',
            [[
                'codigo_nivel'      => '999999',
                'descripcion_nivel' => 'Codigo sin equivalencia',
                'importe'           => 100,
            ]]
        );

        Livewire::actingAs($this->usuarioConRol('facturista'))
            ->test(\App\Livewire\OrdenesPagosAudit\OrdenPagoAuditIndex::class)
            ->set('documentoAuditarId', $documento->id)
            ->call('auditar')
            ->assertSee('Auditoría cancelada')
            ->assertSee('999999')
            ->assertSee('Codigo sin equivalencia')
            ->assertSet('inconsistenciasAuditoria', fn ($items) => count($items) === 1);

        $this->assertSame(0, OrdenPagoAuditCab::count());
    }

    /**
     * Crea una cobertura, un parte y un consumo para la obra social indicada.
     */
    private function crearParteParaObraSocial(Profesional $profesional, string $atencion, string $codUom): void
    {
        $cobertura = Cobertura::create([
            'nombre'  => 'COB '.$codUom,
            'sigla'   => 'COB'.$codUom,
            'cuit'    => '30-123456'.$codUom,
            'cod_uom' => $codUom,
        ]);

        $parte = ParteCab::create([
            'profesional_id' => $profesional->id,
            'atencion'       => $atencion,
            'fec_prestacion' => '2026-06-11 09:00:00',
        ]);
        $parte->cobertura_id = $cobertura->id;
        $parte->save();

        $consumoCab = new ConsumoCab();
        $consumoCab->parte_cab_id = $parte->id;
        $consumoCab->save();

        $this->consumo($consumoCab->id, '600011', 'Especial F', 100);
    }

    /**
     * Atencion del archivo con un unico detalle.
     *
     * @return array<string, mixed>
     */
    private function atencionArchivo(string $atencion, string $obraSocial): array
    {
        return [
            'atencion'    => $atencion,
            'fecha'       => '11/06/2026',
            'obra_social' => $obraSocial,
            'detalles'    => [[
                'fecha'             => '11/06/2026',
                'codigo_nivel'      => '600011',
                'descripcion_nivel' => 'Especial F',
                'importe'           => 100,
            ]],
        ];
    }

    private function crearParteConConsumo(string $nombreCobertura, ?string $codUom): void    {
        $profesional = Profesional::create([
            'nombre' => 'PEREZ, JUAN',
            'cuit'   => '20111222333',
        ]);

        $cobertura = Cobertura::create([
            'nombre'  => $nombreCobertura,
            'sigla'   => $nombreCobertura,
            'cuit'    => '30-12345678-9',
            'cod_uom' => $codUom,
        ]);

        $parte = ParteCab::create([
            'profesional_id' => $profesional->id,
            'atencion'       => '1180759',
            'fec_prestacion' => '2026-06-11 09:00:00',
        ]);
        $parte->cobertura_id = $cobertura->id;
        $parte->save();

        $consumoCab = new ConsumoCab();
        $consumoCab->parte_cab_id = $parte->id;
        $consumoCab->save();

        $this->consumo($consumoCab->id, '600011', 'Especial F', 100);
    }

    /**
     * @param  array<int, array<string, mixed>>  $detalles
     */
    private function documentoConDetalles(
        string $uuid,
        string $obraSocial,
        array $detalles,
        string $atencion = '1180759',
        float $totalInformado = 100
    ): DocumentoLlamaparse {
        foreach ($detalles as $indice => $detalle) {
            $detalles[$indice] = array_merge([
                'fecha'   => '11/06/2026',
                'periodo' => '202606',
            ], $detalle);
        }

        return DocumentoLlamaparse::create([
            'uuid'            => $uuid,
            'archivo'         => 'documento.pdf',
            'nombre_original' => 'OP junio.pdf',
            'mime'            => 'application/pdf',
            'estado'          => 'procesado',
            'json'            => [
                'documento' => [
                    'tipo'            => 'detalle_liquidacion',
                    'fecha'           => '11/06/2026',
                    'total_informado' => $totalInformado,
                ],
                'cabecera' => [
                    'profesional' => 'PEREZ, JUAN',
                    'cuit'        => '20111222333',
                ],
                'atenciones' => [[
                    'atencion'    => $atencion,
                    'fecha'       => '11/06/2026',
                    'obra_social' => $obraSocial,
                    'detalles'    => $detalles,
                ]],
            ],
        ]);
    }

    private function consumo(
        int $consumoCabId,
        string $nivel,
        string $descripcion,
        float $valor,
        float $porcentaje = 100
    ): void {
        $nomenclador = Nomenclador::create([
            'nom_padre_id' => 1,
            'codigo'       => $nivel,
            'nivel'        => $nivel,
            'descripcion'  => $descripcion,
        ]);

        $this->consumoDetalle($consumoCabId, $nomenclador->id, $valor, $porcentaje);
    }

    private function consumoDetalle(
        int $consumoCabId,
        int $nomencladorId,
        float $valor,
        float $porcentaje = 100,
        int $nomPadreId = 1,
        int $estadoId = 5
    ): void {
        $detalle = new ConsumoDet();
        $detalle->consumo_cab_id = $consumoCabId;
        $detalle->nom_padre_id = $nomPadreId;
        $detalle->nomenclador_id = $nomencladorId;
        $detalle->porcentaje = $porcentaje;
        $detalle->valor = $valor;
        $detalle->periodo = '202606';
        $detalle->estado_id = $estadoId;
        $detalle->save();
    }

    /**
     * Parte con un unico consumo del sistema (600011) y un archivo con los
     * detalles indicados, para probar la grilla y las acciones sobre las filas.
     *
     * @param  array<int, array<string, mixed>>  $detalles
     * @return array{orden: OrdenPagoAuditCab, consumo: ConsumoDet}
     */
    private function crearAuditoriaDeUnConsumo(
        array $detalles,
        string $uuid,
        float $totalInformado = 100
    ): array {
        $this->crearParteConConsumo('UOM', '143');

        Nivel::create(['nivel' => '600011', 'cod_uom' => '600011', 'descripcion' => 'Especial F']);

        $documento = $this->documentoConDetalles(
            $uuid,
            '143 - UOM',
            $detalles,
            '1180759',
            $totalInformado
        );

        app(OrdenPagoAuditCompareService::class)->auditar($documento);

        return [
            'orden'   => OrdenPagoAuditCab::firstOrFail(),
            'consumo' => ConsumoDet::firstOrFail(),
        ];
    }

    private function usuarioConRol(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->assignRole($rol);

        return $usuario->fresh();
    }

    private function crearTablasPreexistentes(): void
    {
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

        if (! Schema::hasTable('coberturas')) {
            Schema::create('coberturas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre');
                $table->string('sigla')->nullable();
                $table->string('cuit')->nullable();
                $table->string('cod_uom', 10)->nullable();
                $table->integer('porcentaje_adic')->nullable();
                $table->integer('edad_desde')->nullable();
                $table->integer('edad_hasta')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('partes_cab')) {
            Schema::create('partes_cab', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('profesional_id')->nullable();
                $table->integer('cobertura_id')->nullable();
                $table->integer('estado_id')->nullable();
                $table->string('observaciones')->nullable();
                $table->string('atencion', 10)->nullable();
                $table->dateTime('fec_prestacion')->nullable();
                $table->dateTime('fec_prestacion_fin')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('estados')) {
            Schema::create('estados', function (Blueprint $table) {
                $table->increments('id');
                $table->string('descripcion');
                $table->string('extra')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('consumos_cab')) {
            Schema::create('consumos_cab', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parte_cab_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('consumos_det')) {
            Schema::create('consumos_det', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('consumo_cab_id')->nullable();
                $table->integer('nom_padre_id')->nullable();
                $table->integer('nomenclador_id')->nullable();
                $table->float('porcentaje')->default(100);
                $table->decimal('valor', 14, 2)->default(0);
                $table->string('periodo', 10)->nullable();
                $table->integer('estado_id')->nullable();
                $table->string('obs_refac', 250)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('periodos')) {
            Schema::create('periodos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre', 10);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Vista que usa RendicionRepository para ubicar el parte de un consumo.
        $vistaRendiciones = DB::selectOne(
            "SELECT name FROM sqlite_master WHERE type = 'view' AND name = 'v_rendiciones'"
        );

        if ($vistaRendiciones === null) {
            DB::statement(
                'CREATE VIEW v_rendiciones AS
                 SELECT cd.id AS consumos_det_id, cc.parte_cab_id AS parte_cab_id
                 FROM consumos_det cd
                 JOIN consumos_cab cc ON cc.id = cd.consumo_cab_id'
            );
        }

        if (! Schema::hasTable('nomenclador')) {
            Schema::create('nomenclador', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('nom_padre_id')->nullable();
                $table->string('codigo', 20)->nullable();
                $table->string('nivel', 20)->nullable();
                $table->string('descripcion', 200)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('nom_practicas_estudios')) {
            Schema::create('nom_practicas_estudios', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('nom_padre_id')->nullable();
                $table->string('codigo', 20)->nullable();
                $table->string('nombre', 200)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
}
