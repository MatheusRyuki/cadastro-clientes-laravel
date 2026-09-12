<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class E2eGuard
{
    public const DATABASE = 'e2e';

    public const ENVIRONMENT = 'e2e';

    public function __construct(
        private readonly string $environment,
        private readonly string $configuredDatabase,
        private readonly string $actualDatabase,
        private readonly string $token,
    ) {}

    public static function fromApp(): self
    {
        $connection = (string) config('database.default');

        return new self(
            (string) app()->environment(),
            (string) config("database.connections.{$connection}.database"),
            (string) DB::connection()->getDatabaseName(),
            (string) config('app.e2e_token'),
        );
    }

    public function targetsExclusiveE2eDatabase(): bool
    {
        return $this->environment === self::ENVIRONMENT
            && $this->configuredDatabase === self::DATABASE
            && $this->actualDatabase === self::DATABASE;
    }

    public function failureMessage(): string
    {
        return sprintf(
            'Isolamento E2E recusado: env=%s database=%s connection=%s',
            $this->environment,
            $this->configuredDatabase,
            $this->actualDatabase,
        );
    }

    public function assertExclusiveDatabase(): void
    {
        abort_unless(
            $this->environment === self::ENVIRONMENT,
            403,
            'Rotas E2E só existem no ambiente e2e.',
        );

        abort_unless(
            $this->configuredDatabase === self::DATABASE && $this->actualDatabase === self::DATABASE,
            500,
            $this->failureMessage(),
        );
    }

    public function assertAuthorized(?string $providedToken): void
    {
        $this->assertExclusiveDatabase();

        abort_unless(
            $this->token !== '' && hash_equals($this->token, (string) $providedToken),
            403,
            'Token E2E inválido.',
        );
    }

    public function assertPublicDiskIsolated(): void
    {
        $root = config('filesystems.disks.public.root');

        abort_unless(
            is_string($root) && str_contains($root, 'e2e-public'),
            500,
            'Disco público E2E não isolado.',
        );
    }
}
