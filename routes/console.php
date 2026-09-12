<?php

use App\Support\E2eGuard;
use Illuminate\Support\Facades\Artisan;

Artisan::command('e2e:migrate-fresh {--force : Força a execução}', function (): int {
    $guard = E2eGuard::fromApp();

    if (! $guard->targetsExclusiveE2eDatabase()) {
        $this->error($guard->failureMessage());

        return 1;
    }

    $this->call('migrate:fresh', ['--force' => true]);

    return 0;
})->purpose('Recria o schema somente quando o banco efetivo for e2e');
