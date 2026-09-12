<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_json_results(): void
    {
        $customer = Customer::factory()->create([
            'first_name' => 'Busca',
            'last_name' => 'Unica',
            'email' => 'busca.unica@example.com',
        ]);

        $response = $this->getJson(route('customers.search', ['q' => 'Busca']));

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => $customer->id,
                'name' => $customer->full_name,
                'email' => $customer->email,
                'url' => route('customers.index', ['selected' => $customer->id]),
            ]);
    }

    public function test_search_returns_empty_array_for_short_query(): void
    {
        Customer::factory()->create(['first_name' => 'Curto']);

        $response = $this->getJson(route('customers.search', ['q' => 'C']));

        $response
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_search_finds_customers_by_phone(): void
    {
        $customer = Customer::factory()->create(['phone' => '11999887766']);

        $response = $this->getJson(route('customers.search', ['q' => '998877']));

        $response
            ->assertOk()
            ->assertJsonFragment(['id' => $customer->id]);
    }

    public function test_search_finds_customers_by_ban(): void
    {
        $customer = Customer::factory()->create(['ban' => '5555666677778888']);

        $response = $this->getJson(route('customers.search', ['q' => '666677']));

        $response
            ->assertOk()
            ->assertJsonFragment(['id' => $customer->id]);
    }

    public function test_search_limits_results_to_ten_customers(): void
    {
        Customer::factory()->count(11)->create([
            'first_name' => 'Limite',
            'last_name' => 'Busca',
        ]);

        $response = $this->getJson(route('customers.search', ['q' => 'Limite']));

        $response->assertOk();
        $this->assertCount(10, $response->json());
    }

    public function test_search_orders_results_by_newest_first(): void
    {
        $older = Customer::factory()->create([
            'first_name' => 'Ordem',
            'last_name' => 'Antigo',
            'created_at' => now()->subDays(2),
        ]);
        $newer = Customer::factory()->create([
            'first_name' => 'Ordem',
            'last_name' => 'Recente',
            'created_at' => now()->subDay(),
        ]);

        $response = $this->getJson(route('customers.search', ['q' => 'Ordem']));

        $response->assertOk();

        $ids = collect($response->json())->pluck('id')->all();

        $this->assertSame([$newer->id, $older->id], $ids);
    }
}
