@extends('layouts.customers')

@php
    $pageTitle = __('customers.edit_title');
    $breadcrumbs = [
        ['label' => __('customers.breadcrumbs.customers'), 'url' => route('customers.index')],
        ['label' => $customer->full_name, 'url' => route('customers.show', $customer)],
        ['label' => __('customers.breadcrumbs.edit'), 'current' => true],
    ];
@endphp

@section('customers-content')
    @include('customers.partials.form-shell', [
        'title' => __('customers.edit_title'),
        'backHref' => route('customers.show', $customer),
        'action' => route('customers.update', $customer),
        'method' => 'PUT',
        'customer' => $customer,
        'submitLabel' => __('customers.buttons.update'),
    ])
@endsection
