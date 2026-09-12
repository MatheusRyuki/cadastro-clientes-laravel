@use('Illuminate\Support\Js')

@extends('layouts.customers')

@php
    $pageTitle = __('customers.show_title', ['name' => $customer->full_name]);
    $breadcrumbs = [
        ['label' => __('customers.breadcrumbs.customers'), 'url' => route('customers.index')],
        ['label' => $customer->full_name, 'current' => true],
    ];
@endphp

@section('customers-content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-3xl font-semibold text-gray-900">{{ $customer->full_name }}</h1>
    </div>

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <x-btn variant="secondary" href="{{ route('customers.index') }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            {{ __('customers.back') }}
        </x-btn>

        <x-btn href="{{ route('customers.edit', $customer) }}">
            {{ __('customers.buttons.edit') }}
        </x-btn>

        <form
            x-data
            x-ref="deleteForm"
            method="POST"
            action="{{ route('customers.destroy', $customer) }}"
            class="inline"
        >
            @csrf
            @method('DELETE')
            <x-btn
                variant="destructive"
                type="button"
                @click.prevent="$dispatch('open-confirm-modal', {
                    title: {{ Js::from(__('customers.confirm.delete')) }},
                    message: {{ Js::from(__('customers.confirm.delete_description')) }},
                    confirmLabel: {{ Js::from(__('customers.buttons.delete')) }},
                    cancelLabel: {{ Js::from(__('customers.buttons.cancel')) }},
                    variant: 'destructive',
                    form: $refs.deleteForm
                })"
            >
                {{ __('customers.buttons.delete') }}
            </x-btn>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center gap-6 bg-gray-600 px-6 py-6">
            <x-profile-avatar
                :src="$customer->imageUrl()"
                :alt="$customer->full_name"
                :size="112"
                variant="header"
            />

            <div>
                <h2 class="text-2xl font-semibold text-white">{{ $customer->full_name }}</h2>
                <p class="text-sm text-gray-300">{{ $customer->email }}</p>
                <p class="text-sm text-gray-300">{{ $customer->formatted_phone }}</p>
            </div>
        </div>

        <div class="px-6 py-6">
            <div class="overflow-hidden rounded border border-gray-200">
                <x-detail-row :label="__('customers.fields.ban')" :value="$customer->ban" />
                <x-detail-row :label="__('customers.fields.about')" :value="$customer->about" />
            </div>

            @include('customers.partials.history-timeline', ['customer' => $customer, 'showDeleted' => true])
        </div>
    </div>
@endsection
