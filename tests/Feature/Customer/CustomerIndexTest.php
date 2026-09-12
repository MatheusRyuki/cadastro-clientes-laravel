<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_customers_index(): void
    {
        $this->get('/')->assertRedirect(route('customers.index'));
    }

    public function test_index_returns_successful_response(): void
    {
        $response = $this->get(route('customers.index'));

        $response->assertOk();
    }

    public function test_index_filters_customers_by_search_term(): void
    {
        $matching = Customer::factory()->create(['first_name' => 'Filtrado']);
        Customer::factory()->create(['first_name' => 'Outro']);

        $response = $this->get(route('customers.index', ['search' => 'Filtrado']));

        $response->assertOk();
        $response->assertViewHas('customers', function ($paginator) use ($matching) {
            return $paginator->count() === 1
                && $paginator->contains('id', $matching->id);
        });
        $response->assertViewHas('search', 'Filtrado');
    }

    public function test_index_sorts_customers_by_oldest_first(): void
    {
        $older = Customer::factory()->create(['created_at' => now()->subDays(2)]);
        $newer = Customer::factory()->create(['created_at' => now()->subDay()]);

        $response = $this->get(route('customers.index', ['sort' => 'oldest']));

        $response->assertOk();
        $response->assertViewHas('customers', function ($paginator) use ($older, $newer) {
            return $paginator->first()->is($older)
                && $paginator->last()->is($newer);
        });
        $response->assertViewHas('sort', 'oldest');
    }

    public function test_index_passes_selected_customer_when_id_is_in_results(): void
    {
        $customer = Customer::factory()->create(['first_name' => 'Selecionado']);

        $response = $this->get(route('customers.index', ['selected' => $customer->id]));

        $response->assertOk();
        $response->assertViewHas('selectedCustomer', fn (?Customer $selected) => $selected?->is($customer));
    }
}
