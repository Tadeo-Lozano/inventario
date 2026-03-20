<?php

namespace Tests\Feature;

use App\Models\Balata;
use App\Models\Tarima;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_balata_images_are_stored_on_the_configured_media_disk(): void
    {
        Storage::fake('s3');
        config(['filesystems.media_disk' => 's3']);

        $user = User::factory()->create();
        $tarima = Tarima::create([
            'numero_identificacion' => 'T-001',
        ]);

        $response = $this->actingAs($user)->post(route('balatas.store'), [
            'codigo' => 'BAL-001',
            'marca' => 'Brembo',
            'calidad' => 'Premium',
            'posicion' => 'Delantera',
            'vehiculos' => 'Nissan Versa 2020',
            'cantidad' => 10,
            'precio_inventario' => 100,
            'precio_venta' => 150,
            'tarima_id' => $tarima->id,
            'imagenes' => [
                UploadedFile::fake()->image('balata.jpg'),
            ],
        ]);

        $response->assertRedirect(route('balatas.index'));

        $balata = Balata::query()->where('codigo', 'BAL-001')->firstOrFail();
        $rutaImagen = $balata->imagenes()->value('ruta');

        $this->assertNotNull($rutaImagen);
        Storage::disk('s3')->assertExists($rutaImagen);
    }

    public function test_media_route_reads_files_from_the_configured_media_disk(): void
    {
        Storage::fake('s3');
        config(['filesystems.media_disk' => 's3']);

        $user = User::factory()->create();
        Storage::disk('s3')->put('balatas/demo.txt', 'contenido demo');

        $response = $this->actingAs($user)->get(route('media.show', ['path' => 'balatas/demo.txt']));

        $response->assertOk();
        $this->assertSame('contenido demo', $response->streamedContent());
    }

    public function test_balata_destroy_removes_images_from_the_configured_media_disk(): void
    {
        Storage::fake('s3');
        config(['filesystems.media_disk' => 's3']);

        $user = User::factory()->create();
        $tarima = Tarima::create([
            'numero_identificacion' => 'T-002',
        ]);

        $balata = Balata::create([
            'codigo' => 'BAL-002',
            'marca' => 'Bosch',
            'calidad' => 'Estandar',
            'posicion' => 'Trasera',
            'vehiculos' => 'Chevrolet Aveo 2021',
            'cantidad' => 5,
            'precio_inventario' => 80,
            'precio_venta' => 120,
            'tarima_id' => $tarima->id,
        ]);

        Storage::disk('s3')->put('balatas/demo-delete.txt', 'contenido demo');
        $balata->imagenes()->create([
            'ruta' => 'balatas/demo-delete.txt',
        ]);

        $response = $this->actingAs($user)->delete(route('balatas.destroy', $balata));

        $response->assertRedirect(route('balatas.index'));
        Storage::disk('s3')->assertMissing('balatas/demo-delete.txt');
    }
}
