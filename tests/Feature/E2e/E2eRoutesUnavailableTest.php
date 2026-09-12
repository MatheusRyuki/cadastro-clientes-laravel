<?php

namespace Tests\Feature\E2e;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2eRoutesUnavailableTest extends TestCase
{
    use RefreshDatabase;

    public function test_e2e_health_is_not_available_in_testing_environment(): void
    {
        $this->get('/__e2e/health')->assertNotFound();
    }

    public function test_e2e_reset_without_token_does_not_exist_and_does_not_delete_customers(): void
    {
        $customer = Customer::factory()->create(['email' => 'permanece@example.com']);

        $this->post('/__e2e/reset')->assertNotFound();
        $this->postJson('/__e2e/reset', [], ['X-E2E-Token' => 'qualquer'])->assertNotFound();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'email' => 'permanece@example.com',
        ]);
    }

    public function test_e2e_seed_route_is_not_available_in_testing_environment(): void
    {
        $this->postJson('/__e2e/customers', [
            'customers' => [[
                'first_name' => 'Injetado',
                'last_name' => 'E2E',
                'email' => 'injetado@example.com',
                'phone' => '11900000000',
                'ban' => '1111111111111111',
            ]],
        ], ['X-E2E-Token' => 'qualquer'])->assertNotFound();

        $this->assertDatabaseMissing('customers', ['email' => 'injetado@example.com']);
    }
}
