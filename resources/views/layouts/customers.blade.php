@extends('layouts.app')

@section('title', $pageTitle ?? __('customers.title'))

@section('content')
    <x-toast-stack />
    <x-modal />
    <x-drawer />
    <x-command-palette />

    <x-customer-nav-tabs :active="$activeTab ?? 'customers'" :trash-count="$trashCount ?? 0" />

    @isset($breadcrumbs)
        <x-breadcrumbs :items="$breadcrumbs" />
    @endisset

    @if (($activeTab ?? 'customers') === 'trash')
        <div
            role="status"
            class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
        >
            {{ __('customers.trash_banner') }}
        </div>
    @endif

    <div x-data>
        @yield('customers-content')
    </div>
@endsection
