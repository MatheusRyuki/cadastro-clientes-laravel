<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_customer_details(): void
    {
        $customer = Customer::factory()->create([
            'first_name' => 'Ana',
            'last_name' => 'Costa',
        ]);

        $response = $this->get(route('customers.show', $customer));

        $response
            ->assertOk()
            ->assertViewIs('customers.show')
            ->assertViewHas('customer', fn (Customer $viewCustomer) => $viewCustomer->is($customer));
    }
}
