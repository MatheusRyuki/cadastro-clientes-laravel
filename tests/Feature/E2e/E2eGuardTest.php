<?php

namespace Tests\Feature\E2e;

use App\Models\Customer;
use App\Support\E2eGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class E2eGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_assert_exclusive_database_rejects_non_e2e_environment(): void
    {
        $guard = new E2eGuard('testing', 'e2e', 'e2e', 'secret');

        try {
            $guard->assertExclusiveDatabase();
            $this->fail('Deveria recusar ambiente que não é e2e.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_assert_exclusive_database_rejects_configured_or_actual_mismatch(): void
    {
        $guard = new E2eGuard('e2e', 'laravel', 'e2e', 'secret');

        try {
            $guard->assertExclusiveDatabase();
            $this->fail('Deveria recusar banco configurado diferente de e2e.');
        } catch (HttpException $exception) {
            $this->assertSame(500, $exception->getStatusCode());
        }

        $guard = new E2eGuard('e2e', 'e2e', 'testing', 'secret');

        try {
            $guard->assertExclusiveDatabase();
            $this->fail('Deveria recusar conexão efetiva diferente de e2e.');
        } catch (HttpException $exception) {
            $this->assertSame(500, $exception->getStatusCode());
        }
    }

    public function test_assert_authorized_rejects_missing_or_invalid_token(): void
    {
        $guard = new E2eGuard('e2e', 'e2e', 'e2e', 'secret-token');

        try {
            $guard->assertAuthorized(null);
            $this->fail('Deveria recusar token ausente.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        try {
            $guard->assertAuthorized('token-errado');
            $this->fail('Deveria recusar token inválido.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_from_app_in_phpunit_does_not_target_e2e_database(): void
    {
        $guard = E2eGuard::fromApp();

        $this->assertFalse($guard->targetsExclusiveE2eDatabase());
        $this->assertStringContainsString('testing', $guard->failureMessage());
    }

    public function test_migrate_fresh_command_aborts_outside_e2e_without_dropping_tables(): void
    {
        $customer = Customer::factory()->create(['email' => 'nao-apagar@example.com']);

        $this->artisan('e2e:migrate-fresh')
            ->expectsOutputToContain('Isolamento E2E recusado')
            ->assertExitCode(1);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'email' => 'nao-apagar@example.com',
        ]);
    }
}
