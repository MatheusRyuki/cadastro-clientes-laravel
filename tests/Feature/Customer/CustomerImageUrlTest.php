<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerImageUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_url_uses_default_public_storage_prefix_outside_e2e(): void
    {
        config([
            'app.url' => 'http://localhost:8080',
            'filesystems.disks.public.root' => storage_path('app/public'),
            'filesystems.disks.public.url' => 'http://localhost:8080/storage',
        ]);
        $this->app->make('filesystem')->forgetDisk('public');

        $this->assertNull(env('FILESYSTEM_PUBLIC_PATH'));

        $customer = new Customer(['image' => 'customers/ana.jpg']);

        $this->assertSame('http://localhost:8080/storage/customers/ana.jpg', $customer->imageUrl());
        $this->assertStringNotContainsString('e2e-storage', $customer->imageUrl());
    }

    public function test_show_renders_default_storage_url_for_customer_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('customers/foto.jpg', 'conteudo');

        $customer = Customer::factory()->create([
            'first_name' => 'Ana',
            'last_name' => 'Foto',
            'image' => 'customers/foto.jpg',
        ]);

        $url = $customer->imageUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('/storage/customers/foto.jpg', $url);
        $this->assertStringNotContainsString('e2e-storage', $url);

        $this->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee($url, false)
            ->assertSee('alt="Ana Foto"', false);
    }
}
