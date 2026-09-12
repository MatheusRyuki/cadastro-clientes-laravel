@props([
    'title',
    'backHref',
    'action',
    'method' => 'POST',
    'customer' => null,
    'submitLabel',
])

<h1 class="mb-6 text-3xl font-semibold text-gray-900">{{ $title }}</h1>

<x-error-summary />

<div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 px-6 py-4">
        <x-btn variant="secondary" :href="$backHref">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            {{ __('customers.back') }}
        </x-btn>
    </div>

    <form
        method="POST"
        action="{{ $action }}"
        enctype="multipart/form-data"
        x-data="formSubmit"
        @submit="handleSubmit($event)"
        class="space-y-6 px-6 pt-6 pb-10"
    >
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        @include('customers._form', [
            'customer' => $customer,
            'submitLabel' => $submitLabel,
        ])
    </form>
</div>
