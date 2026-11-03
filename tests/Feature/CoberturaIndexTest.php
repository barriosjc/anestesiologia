<?php

namespace Tests\Feature;

use App\Livewire\Entidades\CoberturaIndex;
use App\Models\Cobertura;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CoberturaIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super-admin', 'web');
        Permission::findOrCreate('adm_entidades', 'web');

        // La tabla "coberturas" es preexistente del sistema y no tiene migracion.
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

        Cobertura::create([
            'nombre'  => 'Medicina Prepaga',
            'sigla'   => 'MEDIPRE',
            'cuit'    => '30123456789',
            'cod_uom' => '78',
        ]);

        Cobertura::create([
            'nombre'  => 'Obra Social Palermo',
            'sigla'   => 'PALERMO',
            'cuit'    => '30765432109',
            'cod_uom' => '79',
        ]);
    }

    public function test_busca_por_nombre_sin_acentos(): void
    {
        Livewire::actingAs($this->usuario())
            ->test(CoberturaIndex::class)
            ->set('text', 'medicina')
            ->assertSee('Medicina Prepaga')
            ->assertDontSee('Obra Social Palermo');
    }

    public function test_busca_por_nombre_con_acentos(): void
    {
        Livewire::actingAs($this->usuario())
            ->test(CoberturaIndex::class)
            ->set('text', 'PREPAGA')
            ->assertSee('Medicina Prepaga')
            ->assertDontSee('Obra Social Palermo');
    }

    public function test_busca_por_sigla(): void
    {
        Livewire::actingAs($this->usuario())
            ->test(CoberturaIndex::class)
            ->set('text', 'palermo')
            ->assertSee('Obra Social Palermo')
            ->assertDontSee('Medicina Prepaga');
    }

    public function test_busca_por_cuit_con_y_sin_guiones(): void
    {
        Livewire::actingAs($this->usuario())
            ->test(CoberturaIndex::class)
            ->set('text', '30123456789')
            ->assertSee('Medicina Prepaga')
            ->assertDontSee('Obra Social Palermo');

        Livewire::actingAs($this->usuario())
            ->test(CoberturaIndex::class)
            ->set('text', '30-12345678-9')
            ->assertSee('Medicina Prepaga')
            ->assertDontSee('Obra Social Palermo');
    }

    public function test_busca_por_cod_uom(): void
    {
        Livewire::actingAs($this->usuario())
            ->test(CoberturaIndex::class)
            ->set('text', '79')
            ->assertSee('Obra Social Palermo')
            ->assertDontSee('Medicina Prepaga');
    }

    public function test_sin_busqueda_muestra_todas_las_coberturas(): void
    {
        Livewire::actingAs($this->usuario())
            ->test(CoberturaIndex::class)
            ->assertSee('Medicina Prepaga')
            ->assertSee('Obra Social Palermo');
    }

    private function usuario(): User
    {
        $usuario = User::factory()->create();
        $usuario->assignRole('super-admin');

        return $usuario->fresh();
    }
}
