<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Customer\Concerns\InteractsWithCustomerPayload;
use Tests\TestCase;

class CustomerSoftDeleteTest extends TestCase
{
    use InteractsWithCustomerPayload, RefreshDatabase;

    public function test_destroy_soft_deletes_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->delete(route('customers.destroy', $customer));

        $response
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('success', __('customers.flash.deleted'));

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_soft_deleted_customer_is_not_listed_on_index(): void
    {
        $customer = Customer::factory()->create(['first_name' => 'Excluido']);

        $this->delete(route('customers.destroy', $customer));

        $response = $this->get(route('customers.index'));

        $response->assertOk();
        $response->assertViewHas('customers', function ($paginator) use ($customer) {
            return $paginator->doesntContain('id', $customer->id);
        });
    }

    public function test_soft_deleted_customer_appears_in_trash(): void
    {
        $customer = Customer::factory()->create(['first_name' => 'NaLixeira']);

        $this->delete(route('customers.destroy', $customer));

        $response = $this->get(route('customers.trash'));

        $response->assertOk();
        $response->assertViewHas('customers', function ($paginator) use ($customer) {
            return $paginator->contains('id', $customer->id);
        });
    }

    public function test_restore_brings_customer_back_to_index(): void
    {
        $customer = Customer::factory()->create(['first_name' => 'Restaurado']);
        $customer->delete();

        $response = $this->patch(route('customers.restore', $customer->id));

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('success', __('customers.flash.restored'));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'deleted_at' => null,
        ]);

        $indexResponse = $this->get(route('customers.index'));

        $indexResponse->assertViewHas('customers', function ($paginator) use ($customer) {
            return $paginator->contains('id', $customer->id);
        });
    }

    public function test_restore_fails_when_customer_is_not_in_trash(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->patch(route('customers.restore', $customer->id));

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('error', __('customers.flash.restore_failed'));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'deleted_at' => null,
        ]);
    }

    public function test_force_destroy_permanently_removes_customer_from_trash(): void
    {
        $customer = Customer::factory()->create();
        $customer->delete();

        $response = $this->delete(route('customers.force-destroy', $customer->id));

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('success', __('customers.flash.force_deleted'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_force_destroy_deletes_customer_image_from_storage(): void
    {
        Storage::fake('public');

        $path = 'customers/avatar.jpg';
        Storage::disk('public')->put($path, 'conteudo');

        $customer = Customer::factory()->create(['image' => $path]);
        $customer->delete();

        $response = $this->delete(route('customers.force-destroy', $customer->id));

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('success', __('customers.flash.force_deleted'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_new_customer_can_use_email_after_soft_delete(): void
    {
        $email = 'reused@example.com';

        $customer = Customer::factory()->create(['email' => $email]);
        $customer->delete();

        $response = $this->post(route('customers.store'), $this->validCustomerPayload([
            'email' => $email,
        ]));

        $newCustomer = Customer::query()->where('email', $email)->first();

        $this->assertNotNull($newCustomer);
        $this->assertNotSame($customer->id, $newCustomer->id);
        $this->assertNull($newCustomer->deleted_at);

        $response
            ->assertRedirect(route('customers.show', $newCustomer))
            ->assertSessionHas('success', __('customers.flash.created'));
    }
}
