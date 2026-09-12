<?php

use App\Models\Customer;
use App\Support\E2eGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

Route::get('/__e2e/health', function (): JsonResponse {
    E2eGuard::fromApp()->assertExclusiveDatabase();

    return response()->json([
        'env' => app()->environment(),
        'database' => config('database.connections.'.config('database.default').'.database'),
        'connection' => DB::connection()->getDatabaseName(),
    ]);
});

Route::post('/__e2e/reset', function (Request $request): JsonResponse {
    $guard = E2eGuard::fromApp();
    $guard->assertAuthorized($request->header('X-E2E-Token'));
    $guard->assertPublicDiskIsolated();

    if (Schema::hasTable('customers')) {
        Customer::withTrashed()->forceDelete();
    }

    File::cleanDirectory((string) config('filesystems.disks.public.root'));

    return response()->json([
        'ok' => true,
        'database' => DB::connection()->getDatabaseName(),
    ]);
});

Route::post('/__e2e/customers', function (Request $request): JsonResponse {
    E2eGuard::fromApp()->assertAuthorized($request->header('X-E2E-Token'));

    $customers = $request->collect('customers');

    $created = $customers->map(function (array $attributes): Customer {
        $customer = Customer::query()->create($attributes);

        if (isset($attributes['created_at'])) {
            $customer->created_at = $attributes['created_at'];
            $customer->saveQuietly();
        }

        if (! empty($attributes['trashed'])) {
            $customer->delete();
        }

        return $customer->fresh() ?? $customer;
    });

    return response()->json([
        'database' => DB::connection()->getDatabaseName(),
        'customers' => $created->map(fn (Customer $customer) => [
            'id' => $customer->id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'email' => $customer->email,
            'deleted_at' => $customer->deleted_at,
        ]),
    ]);
});
