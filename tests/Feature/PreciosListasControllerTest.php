<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Centro;
use App\Models\Periodo;
use App\Models\Cobertura;
use App\Models\Gerenciadora;
use App\Models\Valores_cab;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PreciosListasControllerTest extends TestCase
{
    use RefreshDatabase; // Limpia la BD después de cada test

    /** @test */
    public function it_loads_the_list_page_without_tipo()
    {
        // Crear datos de prueba
        Gerenciadora::factory()->create();
        Cobertura::factory()->create();
        Centro::factory()->create();
        Periodo::factory()->create();
        Valores_cab::factory()->create();

        // Llamar a la ruta sin tipo
        $response = $this->get(route('precios.index'));

        // Verificar que la respuesta sea 200 OK
        $response->assertStatus(200);
        
        // Verificar que la vista tiene los datos
        $response->assertViewHas('listas');
        $response->assertViewHas('gerenciadoras');
        $response->assertViewHas('coberturas');
        $response->assertViewHas('centros');
        $response->assertViewHas('periodos');
    }

    /** @test */
    public function it_filters_list_by_tipo()
    {
        $tipo = 1; // Simula un ID de cobertura

        // Crear cobertura con el tipo dado
        $cobertura = Cobertura::factory()->create(['nom_padre_id' => $tipo]);

        // Llamar a la ruta con el parámetro tipo
        $response = $this->get(route('nomenclador/listas/', ['tipo' => $tipo]));

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que solo aparecen coberturas del tipo dado
        $response->assertViewHas('coberturas', function ($coberturas) use ($tipo) {
            return $coberturas->every(fn($c) => $c->nom_padre_id == $tipo);
        });
    }
}
