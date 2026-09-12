<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCreateEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_returns_successful_response(): void
    {
        $response = $this->get(route('customers.create'));

        $response
            ->assertOk()
            ->assertViewIs('customers.create');
    }

    public function test_create_with_drawer_flag_returns_drawer_form_partial(): void
    {
        $response = $this->get(route('customers.create', ['drawer' => 1]));

        $response
            ->assertOk()
            ->assertViewIs('customers.partials.drawer-form');
    }

    public function test_edit_page_returns_successful_response(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->get(route('customers.edit', $customer));

        $response
            ->assertOk()
            ->assertViewIs('customers.edit')
            ->assertViewHas('customer', fn (Customer $viewCustomer) => $viewCustomer->is($customer));
    }

    public function test_edit_with_drawer_flag_returns_drawer_form_partial(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->get(route('customers.edit', ['customer' => $customer, 'drawer' => 1]));

        $response
            ->assertOk()
            ->assertViewIs('customers.partials.drawer-form')
            ->assertViewHas('customer', fn (Customer $viewCustomer) => $viewCustomer->is($customer));
    }
}
