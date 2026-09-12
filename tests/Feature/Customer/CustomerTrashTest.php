<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerTrashTest extends TestCase
{
    use RefreshDatabase;

    public function test_trash_page_returns_successful_response(): void
    {
        $response = $this->get(route('customers.trash'));

        $response->assertOk();
    }

    public function test_trash_filters_customers_by_search_term(): void
    {
        $matching = Customer::factory()->create(['first_name' => 'LixeiraFiltrada']);
        $matching->delete();
        Customer::factory()->create(['first_name' => 'Ativo'])->delete();

        $response = $this->get(route('customers.trash', ['search' => 'LixeiraFiltrada']));

        $response->assertOk();
        $response->assertViewHas('customers', function ($paginator) use ($matching) {
            return $paginator->count() === 1
                && $paginator->contains('id', $matching->id);
        });
    }

    public function test_trash_sorts_customers_by_oldest_first(): void
    {
        $older = Customer::factory()->create(['created_at' => now()->subDays(2)]);
        $newer = Customer::factory()->create(['created_at' => now()->subDay()]);
        $older->delete();
        $newer->delete();

        $response = $this->get(route('customers.trash', ['sort' => 'oldest']));

        $response->assertOk();
        $response->assertViewHas('customers', function ($paginator) use ($older, $newer) {
            return $paginator->first()->is($older)
                && $paginator->last()->is($newer);
        });
    }

    public function test_bulk_restore_without_selection_flashes_error(): void
    {
        $response = $this->post(route('customers.bulk-restore'), [
            'ids' => [],
        ]);

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('error', __('customers.flash.bulk_none_selected'));
    }

    public function test_bulk_restore_restores_selected_trashed_customers(): void
    {
        $first = Customer::factory()->create();
        $second = Customer::factory()->create();
        $first->delete();
        $second->delete();

        $response = $this->post(route('customers.bulk-restore'), [
            'ids' => [$first->id, $second->id],
        ]);

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('success', __('customers.flash.bulk_restored', ['count' => 2]));

        $this->assertDatabaseHas('customers', ['id' => $first->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('customers', ['id' => $second->id, 'deleted_at' => null]);
    }

    public function test_bulk_restore_without_matching_ids_flashes_error(): void
    {
        $active = Customer::factory()->create();

        $response = $this->post(route('customers.bulk-restore'), [
            'ids' => [$active->id],
        ]);

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('error', __('customers.flash.bulk_none_matched'));
    }

    public function test_bulk_force_destroy_without_selection_flashes_error(): void
    {
        $response = $this->delete(route('customers.bulk-force-destroy'), [
            'ids' => [],
        ]);

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('error', __('customers.flash.bulk_none_selected'));
    }

    public function test_bulk_force_destroy_without_matching_ids_flashes_error(): void
    {
        $active = Customer::factory()->create();

        $response = $this->delete(route('customers.bulk-force-destroy'), [
            'ids' => [$active->id],
        ]);

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('error', __('customers.flash.bulk_none_matched'));
    }

    public function test_bulk_force_destroy_permanently_removes_selected_customers_and_images(): void
    {
        Storage::fake('public');

        $firstPath = 'customers/first.jpg';
        $secondPath = 'customers/second.jpg';
        Storage::disk('public')->put($firstPath, 'img1');
        Storage::disk('public')->put($secondPath, 'img2');

        $first = Customer::factory()->create(['image' => $firstPath]);
        $second = Customer::factory()->create(['image' => $secondPath]);
        $first->delete();
        $second->delete();

        $response = $this->delete(route('customers.bulk-force-destroy'), [
            'ids' => [$first->id, $second->id],
        ]);

        $response
            ->assertRedirect(route('customers.trash'))
            ->assertSessionHas('success', __('customers.flash.bulk_force_deleted', ['count' => 2]));

        $this->assertDatabaseMissing('customers', ['id' => $first->id]);
        $this->assertDatabaseMissing('customers', ['id' => $second->id]);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertMissing($secondPath);
    }
}
