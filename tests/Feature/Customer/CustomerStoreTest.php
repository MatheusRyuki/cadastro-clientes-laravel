<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Customer\Concerns\InteractsWithCustomerPayload;
use Tests\TestCase;

class CustomerStoreTest extends TestCase
{
    use InteractsWithCustomerPayload, RefreshDatabase;

    public function test_store_creates_customer_and_redirects_to_show_with_success_flash(): void
    {
        $payload = $this->validCustomerPayload();

        $response = $this->post(route('customers.store'), $payload);

        $customer = Customer::query()->where('email', $payload['email'])->first();

        $this->assertNotNull($customer);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'email' => $payload['email'],
        ]);

        $response
            ->assertRedirect(route('customers.show', $customer))
            ->assertSessionHas('success', __('customers.flash.created'));
    }

    public function test_store_from_drawer_redirects_to_index_with_selected_customer(): void
    {
        $payload = $this->validCustomerPayload(['from_drawer' => '1']);

        $response = $this->post(route('customers.store'), $payload);

        $customer = Customer::query()->where('email', $payload['email'])->first();

        $this->assertNotNull($customer);

        $response
            ->assertRedirect(route('customers.index', ['selected' => $customer->id]))
            ->assertSessionHas('success', __('customers.flash.created'));
    }

    public function test_store_rejects_duplicate_email(): void
    {
        Customer::factory()->create(['email' => 'duplicado@example.com']);

        $response = $this->from(route('customers.create'))
            ->post(route('customers.store'), $this->validCustomerPayload([
                'email' => 'duplicado@example.com',
            ]));

        $response
            ->assertRedirect(route('customers.create'))
            ->assertSessionHasErrors(['email']);

        $this->assertDatabaseCount('customers', 1);
    }

    public function test_store_validation_fails_with_empty_required_fields(): void
    {
        $response = $this->from(route('customers.create'))
            ->post(route('customers.store'), []);

        $response
            ->assertRedirect(route('customers.create'))
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'phone', 'ban']);

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_store_validation_fails_with_invalid_ban(): void
    {
        $response = $this->from(route('customers.create'))
            ->post(route('customers.store'), $this->validCustomerPayload(['ban' => 'invalid-ban']));

        $response
            ->assertRedirect(route('customers.create'))
            ->assertSessionHasErrors(['ban']);

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_store_validation_fails_with_invalid_email(): void
    {
        $response = $this->from(route('customers.create'))
            ->post(route('customers.store'), $this->validCustomerPayload(['email' => 'nao-e-email']));

        $response
            ->assertRedirect(route('customers.create'))
            ->assertSessionHasErrors(['email']);

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_store_validation_fails_when_first_name_exceeds_max_length(): void
    {
        $response = $this->from(route('customers.create'))
            ->post(route('customers.store'), $this->validCustomerPayload([
                'first_name' => str_repeat('a', 256),
            ]));

        $response
            ->assertRedirect(route('customers.create'))
            ->assertSessionHasErrors(['first_name']);

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_store_validation_fails_when_about_exceeds_max_length(): void
    {
        $response = $this->from(route('customers.create'))
            ->post(route('customers.store'), $this->validCustomerPayload([
                'about' => str_repeat('a', 1001),
            ]));

        $response
            ->assertRedirect(route('customers.create'))
            ->assertSessionHasErrors(['about']);

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_store_validation_fails_with_invalid_image_mime(): void
    {
        Storage::fake('public');

        $response = $this->from(route('customers.create'))
            ->post(route('customers.store'), [
                ...$this->validCustomerPayload(),
                'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ]);

        $response
            ->assertRedirect(route('customers.create'))
            ->assertSessionHasErrors(['image']);

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_store_uploads_image_to_public_disk(): void
    {
        Storage::fake('public');

        $payload = [
            ...$this->validCustomerPayload(['email' => 'com-imagem@example.com']),
            'image' => $this->fakeCustomerImage(),
        ];

        $response = $this->post(route('customers.store'), $payload);

        $customer = Customer::query()->where('email', 'com-imagem@example.com')->first();

        $this->assertNotNull($customer);
        $this->assertNotNull($customer->image);
        Storage::disk('public')->assertExists($customer->image);

        $response->assertRedirect(route('customers.show', $customer));
    }

    public function test_drawer_validation_returns_422_with_from_drawer_flag(): void
    {
        $response = $this->post(route('customers.store'), [
            'from_drawer' => '1',
        ]);

        $response->assertStatus(422);
    }
}
