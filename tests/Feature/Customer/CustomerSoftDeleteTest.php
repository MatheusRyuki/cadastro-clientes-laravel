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

    public function test_restore_unknown_id_flashes_error(): void
    {
        $response = $this->patch(route('customers.restore', 999999));

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('error', __('customers.flash.restore_failed'));
    }

    public function test_force_destroy_unknown_id_returns_not_found(): void
    {
        $this->delete(route('customers.force-destroy', 999999))->assertNotFound();
    }

    public function test_restore_is_blocked_when_active_customer_already_uses_the_email(): void
    {
        $email = 'conflito@example.com';
        $trashed = Customer::factory()->create([
            'first_name' => 'Lixeira',
            'last_name' => 'Original',
            'email' => $email,
            'phone' => '11911112222',
            'ban' => '1111111111111111',
            'about' => 'permanece',
        ]);
        $trashed->delete();
        $active = Customer::factory()->create([
            'first_name' => 'Ativo',
            'email' => $email,
            'phone' => '11933334444',
        ]);

        $response = $this->patch(route('customers.restore', $trashed->id));

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('error', __('customers.flash.restore_email_conflict'));

        $trashed->refresh();
        $active->refresh();

        $this->assertSoftDeleted('customers', ['id' => $trashed->id]);
        $this->assertSame('Lixeira', $trashed->first_name);
        $this->assertSame('Original', $trashed->last_name);
        $this->assertSame($email, $trashed->email);
        $this->assertSame('11911112222', $trashed->phone);
        $this->assertSame('1111111111111111', $trashed->ban);
        $this->assertSame('permanece', $trashed->about);
        $this->assertSame('Ativo', $active->first_name);
        $this->assertSame($email, $active->email);
        $this->assertNull($active->deleted_at);
        $this->assertSame(1, Customer::query()->where('email', $email)->count());
    }

    public function test_restore_uses_the_same_email_comparison_as_unique_validation(): void
    {
        $trashed = Customer::factory()->create(['email' => 'Case@example.com']);
        $trashed->delete();
        Customer::factory()->create(['email' => 'case@example.com']);

        $response = $this->patch(route('customers.restore', $trashed->id));

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('error', __('customers.flash.restore_email_conflict'));

        $this->assertSoftDeleted('customers', ['id' => $trashed->id]);
    }

    public function test_restore_succeeds_when_only_other_trashed_customers_share_the_email(): void
    {
        $email = 'so-lixeira@example.com';
        $first = Customer::factory()->create(['email' => $email, 'first_name' => 'Primeiro']);
        $second = Customer::factory()->create(['email' => $email, 'first_name' => 'Segundo']);
        $first->delete();
        $second->delete();

        $response = $this->patch(route('customers.restore', $first->id));

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('success', __('customers.flash.restored'));

        $this->assertDatabaseHas('customers', ['id' => $first->id, 'deleted_at' => null]);
        $this->assertSoftDeleted('customers', ['id' => $second->id]);
    }

    public function test_restore_succeeds_after_active_email_conflict_is_resolved(): void
    {
        $email = 'depois@example.com';
        $trashed = Customer::factory()->create(['email' => $email, 'first_name' => 'Lixeira']);
        $trashed->delete();
        $active = Customer::factory()->create(['email' => $email, 'first_name' => 'Ativo']);

        $this->put(route('customers.update', $active), $this->validCustomerPayload([
            'first_name' => 'Ativo',
            'email' => 'livre@example.com',
        ]))->assertRedirect(route('customers.show', $active));

        $response = $this->patch(route('customers.restore', $trashed->id));

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('success', __('customers.flash.restored'));

        $this->assertDatabaseHas('customers', ['id' => $trashed->id, 'deleted_at' => null, 'email' => $email]);
        $this->assertDatabaseHas('customers', ['id' => $active->id, 'email' => 'livre@example.com', 'deleted_at' => null]);
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
