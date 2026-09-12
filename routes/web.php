<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/customers');

Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
Route::get('customers/trash', [CustomerController::class, 'trash'])->name('customers.trash');
Route::post('customers/trash/bulk-restore', [CustomerController::class, 'bulkRestore'])->name('customers.bulk-restore');
Route::delete('customers/trash/bulk-force', [CustomerController::class, 'bulkForceDestroy'])->name('customers.bulk-force-destroy');
Route::resource('customers', CustomerController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
Route::patch('customers/{customer}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
Route::delete('customers/{customer}/force', [CustomerController::class, 'forceDestroy'])->name('customers.force-destroy');
