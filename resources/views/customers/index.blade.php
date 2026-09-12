@use('Illuminate\Support\Js')

@extends('layouts.customers')

@php
    $pageTitle = __('customers.title');
    $breadcrumbs = [
        ['label' => __('customers.breadcrumbs.customers'), 'url' => route('customers.index'), 'current' => true],
    ];
    $listParams = array_filter([
        'search' => $search ?: null,
        'sort' => $sort !== 'newest' ? $sort : null,
    ]);
    $closeUrl = route('customers.index', $listParams);
    $emptyState = [
        'message' => $search
            ? __('customers.empty.none_search', ['search' => $search])
            : __('customers.empty.none'),
        'description' => $search ? null : __('customers.empty.none_description'),
        'ctaLabel' => $search ? __('customers.empty.clear_search') : __('customers.empty.create_cta'),
        'ctaUrl' => $search ? route('customers.index') : '#',
        'ctaDrawer' => $search ? null : route('customers.create', ['drawer' => 1]),
    ];
@endphp

@section('customers-content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-3xl font-semibold text-gray-900">{{ __('customers.title') }}</h1>
        <button
            type="button"
            class="hidden cursor-pointer items-center gap-1 rounded border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-500 transition hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2 sm:inline-flex"
            @click="$dispatch('command-palette-open')"
            title="{{ __('customers.command_palette.title') }}"
        >
            {{ __('customers.command_palette.shortcut') }}
        </button>
    </div>

    <div class="md:flex md:gap-6">
        <div @class([
            'overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm',
            'md:w-[40%] md:shrink-0' => $selectedCustomer,
            'w-full' => ! $selectedCustomer,
        ])>
            <div @class([
                'border-b border-gray-200 py-4',
                'flex flex-col gap-3 px-4 md:px-4' => $selectedCustomer,
                'flex flex-col gap-3 px-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between md:px-6' => ! $selectedCustomer,
            ])>
                <x-btn
                    class="shrink-0 self-start"
                    href="#"
                    @click.prevent="$dispatch('open-drawer', { url: '{{ route('customers.create', ['drawer' => 1]) }}', title: {{ Js::from(__('customers.create_title')) }} })"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('customers.create') }}
                </x-btn>

                @include('customers.partials.list-filters', [
                    'action' => route('customers.index'),
                    'search' => $search,
                    'sort' => $sort,
                    'selected' => $selectedCustomer?->id,
                ])
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('customers.columns.created') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('customers.columns.customer') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('customers.fields.phone') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('customers.fields.email') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('customers.columns.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($customers as $customer)
                            @include('customers.partials.list-row', [
                                'customer' => $customer,
                                'layout' => 'desktop',
                                'search' => $search,
                                'sort' => $sort,
                                'selected' => $selectedCustomer?->id,
                            ])
                        @empty
                            <tr>
                                <td colspan="5">
                                    @include('customers.partials.empty-state', $emptyState)
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:hidden">
                @forelse ($customers as $customer)
                    @include('customers.partials.list-row', [
                        'customer' => $customer,
                        'layout' => 'mobile',
                        'search' => $search,
                        'sort' => $sort,
                        'selected' => $selectedCustomer?->id,
                    ])
                @empty
                    @include('customers.partials.empty-state', $emptyState)
                @endforelse
            </div>

            @if ($customers->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>

        @if ($selectedCustomer)
            <div
                class="fixed inset-0 z-40 flex flex-col overflow-hidden bg-white md:hidden"
                @keydown.escape.window="window.location.href = {{ Js::from($closeUrl) }}"
            >
                @include('customers.partials.detail-panel', [
                    'customer' => $selectedCustomer,
                    'closeUrl' => $closeUrl,
                ])
            </div>

            <div
                class="hidden min-h-0 flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm md:flex md:flex-1"
                @keydown.escape.window="window.location.href = {{ Js::from($closeUrl) }}"
            >
                @include('customers.partials.detail-panel', [
                    'customer' => $selectedCustomer,
                    'closeUrl' => $closeUrl,
                ])
            </div>
        @endif
    </div>
@endsection
