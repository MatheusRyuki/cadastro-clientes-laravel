@extends('layouts.customers')

@php
    $pageTitle = __('customers.create_title');
    $breadcrumbs = [
        ['label' => __('customers.breadcrumbs.customers'), 'url' => route('customers.index')],
        ['label' => __('customers.breadcrumbs.create'), 'current' => true],
    ];
@endphp

@section('customers-content')
    @include('customers.partials.form-shell', [
        'title' => __('customers.create_title'),
        'backHref' => route('customers.index'),
        'action' => route('customers.store'),
        'submitLabel' => __('customers.buttons.create'),
    ])
@endsection
