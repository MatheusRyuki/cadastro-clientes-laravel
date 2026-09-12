<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Customer\Concerns\InteractsWithCustomerPayload;
use Tests\TestCase;

class CustomerUpdateTest extends TestCase
{
    use InteractsWithCustomerPayload, RefreshDatabase;

    public function test_update_persists_changes_and_redirects_to_show(): void
    {
        $customer = Customer::factory()->create();

        $payload = $this->validCustomerPayload([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria@example.com',
            'phone' => '21999887766',
            'ban' => '9876543210987654',
        ]);

        $response = $this->put(route('customers.update', $customer), $payload);

        $response
            ->assertRedirect(route('customers.show', $customer))
            ->assertSessionHas('success', __('customers.flash.updated'));

        $customer->refresh();

        $this->assertSame('Maria', $customer->first_name);
        $this->assertSame('Santos', $customer->last_name);
        $this->assertSame('maria@example.com', $customer->email);
        $this->assertSame('21999887766', $customer->phone);
        $this->assertSame('9876543210987654', $customer->ban);
    }

    public function test_update_from_drawer_redirects_to_index_with_selected_customer(): void
    {
        $customer = Customer::factory()->create();

        $payload = $this->validCustomerPayload([
            'first_name' => 'Drawer',
            'from_drawer' => '1',
        ]);

        $response = $this->put(route('customers.update', $customer), $payload);

        $response
            ->assertRedirect(route('customers.index', ['selected' => $customer->id]))
            ->assertSessionHas('success', __('customers.flash.updated'));

        $customer->refresh();
        $this->assertSame('Drawer', $customer->first_name);
    }

    public function test_update_rejects_email_already_used_by_another_customer(): void
    {
        Customer::factory()->create(['email' => 'ocupado@example.com']);
        $customer = Customer::factory()->create(['email' => 'meu@example.com']);

        $response = $this->from(route('customers.edit', $customer))
            ->put(route('customers.update', $customer), $this->validCustomerPayload([
                'email' => 'ocupado@example.com',
            ]));

        $response
            ->assertRedirect(route('customers.edit', $customer))
            ->assertSessionHasErrors(['email']);

        $customer->refresh();
        $this->assertSame('meu@example.com', $customer->email);
    }

    public function test_update_validation_fails_with_invalid_fields(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->from(route('customers.edit', $customer))
            ->put(route('customers.update', $customer), [
                'first_name' => '',
                'last_name' => '',
                'email' => 'invalido',
                'phone' => '',
                'ban' => 'nao-numerico',
            ]);

        $response
            ->assertRedirect(route('customers.edit', $customer))
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'phone', 'ban']);
    }

    public function test_update_validation_fails_when_about_exceeds_max_length(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->from(route('customers.edit', $customer))
            ->put(route('customers.update', $customer), $this->validCustomerPayload([
                'about' => str_repeat('a', 1001),
            ]));

        $response
            ->assertRedirect(route('customers.edit', $customer))
            ->assertSessionHasErrors(['about']);
    }

    public function test_update_keeps_existing_image_when_no_new_file_is_sent(): void
    {
        Storage::fake('public');

        $path = 'customers/manter.jpg';
        Storage::disk('public')->put($path, 'conteudo');

        $customer = Customer::factory()->create([
            'first_name' => 'Original',
            'image' => $path,
        ]);

        $response = $this->put(route('customers.update', $customer), $this->validCustomerPayload([
            'first_name' => 'Renomeado',
        ]));

        $customer->refresh();

        $this->assertSame('Renomeado', $customer->first_name);
        $this->assertSame($path, $customer->image);
        Storage::disk('public')->assertExists($path);
        $response->assertRedirect(route('customers.show', $customer));
    }

    public function test_update_rejects_invalid_image_without_replacing_existing_file(): void
    {
        Storage::fake('public');

        $path = 'customers/valida.jpg';
        Storage::disk('public')->put($path, 'conteudo');

        $customer = Customer::factory()->create([
            'email' => 'com-foto@example.com',
            'image' => $path,
        ]);

        $response = $this->from(route('customers.edit', $customer))
            ->put(route('customers.update', $customer), [
                ...$this->validCustomerPayload(['email' => 'com-foto@example.com']),
                'image' => UploadedFile::fake()->create('malware.exe', 20, 'application/octet-stream'),
            ]);

        $response
            ->assertRedirect(route('customers.edit', $customer))
            ->assertSessionHasErrors(['image']);

        $customer->refresh();
        $this->assertSame($path, $customer->image);
        $this->assertSame('com-foto@example.com', $customer->email);
        Storage::disk('public')->assertExists($path);
    }

    public function test_update_replaces_existing_image_and_deletes_old_file(): void
    {
        Storage::fake('public');

        $oldPath = 'customers/old.jpg';
        Storage::disk('public')->put($oldPath, 'conteudo-antigo');

        $customer = Customer::factory()->create(['image' => $oldPath]);

        $response = $this->put(route('customers.update', $customer), [
            ...$this->validCustomerPayload(),
            'image' => $this->fakeCustomerImage('nova.jpg'),
        ]);

        $customer->refresh();

        $this->assertNotSame($oldPath, $customer->image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($customer->image);

        $response->assertRedirect(route('customers.show', $customer));
    }

    public function test_drawer_validation_returns_422_on_update_with_from_drawer_flag(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->put(route('customers.update', $customer), [
            'from_drawer' => '1',
        ]);

        $response->assertStatus(422);
    }
}
