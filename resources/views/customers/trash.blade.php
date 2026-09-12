@use('Illuminate\Support\Js')

@extends('layouts.customers')

@php
    $pageTitle = __('customers.trash_title');
    $activeTab = 'trash';
    $breadcrumbs = [
        ['label' => __('customers.breadcrumbs.customers'), 'url' => route('customers.index')],
        ['label' => __('customers.breadcrumbs.trash'), 'current' => true],
    ];
    $customerIds = $customers->pluck('id')->all();
    $emptyState = [
        'message' => $search
            ? __('customers.empty.none_search', ['search' => $search])
            : __('customers.empty.trash_none'),
        'description' => $search ? null : __('customers.empty.trash_none_description'),
        'secondaryLabel' => $search ? __('customers.empty.clear_search') : null,
        'secondaryUrl' => $search ? route('customers.trash') : null,
        'ctaLabel' => __('customers.back'),
        'ctaUrl' => route('customers.index'),
    ];
@endphp

@section('customers-content')
    <h1 class="mb-6 text-3xl font-semibold text-gray-900">{{ __('customers.trash_title') }}</h1>

    <div
        x-data="{
            selected: [],
            toggleAll(checked, ids) {
                this.selected = checked ? ids : [];
            },
            toggle(id) {
                if (this.selected.includes(id)) {
                    this.selected = this.selected.filter(i => i !== id);
                } else {
                    this.selected.push(id);
                }
            }
        }"
        class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
    >
        <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between md:px-6">
            <div class="flex flex-wrap items-center gap-3">
                <x-btn variant="secondary" href="{{ route('customers.index') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    {{ __('customers.back') }}
                </x-btn>

                <template x-if="selected.length > 0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm text-gray-500" x-text="{{ Js::from(__('customers.bulk.selected', ['count' => ':count'])) }}.replace(':count', selected.length)"></span>

                        <form x-ref="bulkRestoreForm" method="POST" action="{{ route('customers.bulk-restore') }}">
                            @csrf
                            <template x-for="id in selected" :key="'restore-' + id">
                                <input type="hidden" name="ids[]" :value="id">
                            </template>
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="sort" value="{{ $sort }}">
                            <x-btn
                                variant="secondary"
                                type="button"
                                @click.prevent="$dispatch('open-confirm-modal', {
                                    title: {{ Js::from(__('customers.confirm.bulk_restore', ['count' => ':count'])) }}.replace(':count', selected.length),
                                    message: {{ Js::from(__('customers.confirm.restore_description')) }},
                                    confirmLabel: {{ Js::from(__('customers.buttons.restore_selected')) }},
                                    cancelLabel: {{ Js::from(__('customers.buttons.cancel')) }},
                                    variant: 'primary',
                                    form: $refs.bulkRestoreForm
                                })"
                            >
                                {{ __('customers.buttons.restore_selected') }}
                            </x-btn>
                        </form>

                        <form x-ref="bulkForceForm" method="POST" action="{{ route('customers.bulk-force-destroy') }}">
                            @csrf
                            @method('DELETE')
                            <template x-for="id in selected" :key="'force-' + id">
                                <input type="hidden" name="ids[]" :value="id">
                            </template>
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="sort" value="{{ $sort }}">
                            <x-btn
                                variant="destructive"
                                type="button"
                                @click.prevent="$dispatch('open-confirm-modal', {
                                    title: {{ Js::from(__('customers.confirm.bulk_force_delete', ['count' => ':count'])) }}.replace(':count', selected.length),
                                    message: {{ Js::from(__('customers.confirm.force_delete_description')) }},
                                    confirmLabel: {{ Js::from(__('customers.buttons.force_delete_selected')) }},
                                    cancelLabel: {{ Js::from(__('customers.buttons.cancel')) }},
                                    variant: 'destructive',
                                    form: $refs.bulkForceForm
                                })"
                            >
                                {{ __('customers.buttons.force_delete_selected') }}
                            </x-btn>
                        </form>
                    </div>
                </template>
            </div>

            @include('customers.partials.list-filters', [
                'action' => route('customers.trash'),
                'search' => $search,
                'sort' => $sort,
            ])
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3">
                            <input
                                type="checkbox"
                                class="size-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                aria-label="{{ __('customers.bulk.select_all') }}"
                                @change="toggleAll($event.target.checked, {{ Js::from($customerIds) }})"
                            >
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('customers.columns.created') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('customers.columns.customer') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('customers.fields.phone') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('customers.fields.email') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">{{ __('customers.columns.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">
                                <input
                                    type="checkbox"
                                    class="size-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    value="{{ $customer->id }}"
                                    :checked="selected.includes({{ $customer->id }})"
                                    @change="toggle({{ $customer->id }})"
                                >
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $customer->relative_created_at }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <x-profile-avatar
                                        :src="$customer->imageUrl()"
                                        :alt="$customer->full_name"
                                        :size="40"
                                        variant="form"
                                    />
                                    <span class="text-sm font-medium text-gray-900">{{ $customer->full_name }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $customer->formatted_phone }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $customer->email }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @include('customers.partials.row-actions', [
                                    'customer' => $customer,
                                    'refPrefix' => 'desktopTrash' . $customer->id,
                                    'trash' => true,
                                    'search' => $search,
                                    'sort' => $sort,
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                @include('customers.partials.empty-state', $emptyState)
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden">
            @forelse ($customers as $customer)
                <div class="border-b border-gray-200 p-4">
                    <div class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            class="mt-1 size-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            value="{{ $customer->id }}"
                            :checked="selected.includes({{ $customer->id }})"
                            @change="toggle({{ $customer->id }})"
                        >
                        <x-profile-avatar
                            :src="$customer->imageUrl()"
                            :alt="$customer->full_name"
                            :size="48"
                            variant="form"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-900">{{ $customer->full_name }}</div>
                            <div class="mt-0.5 text-sm text-gray-500">{{ $customer->email }}</div>
                            <div class="mt-0.5 text-sm text-gray-500">{{ $customer->formatted_phone }}</div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2 pl-7">
                        @include('customers.partials.row-actions', [
                            'customer' => $customer,
                            'refPrefix' => 'mobileTrash' . $customer->id,
                            'trash' => true,
                            'search' => $search,
                            'sort' => $sort,
                        ])
                    </div>
                </div>
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
@endsection
