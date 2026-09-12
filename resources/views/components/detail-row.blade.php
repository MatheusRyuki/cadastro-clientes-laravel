@props([
    'label',
    'value' => '',
])

@php
    $isEmpty = blank($value);
    $displayValue = $isEmpty ? __('customers.not_informed') : $value;
@endphp

<div class="flex border-b border-gray-200 last:border-b-0">
    <div class="w-1/3 px-4 py-3 text-sm font-medium text-gray-900">{{ $label }}</div>
    <div @class([
        'flex-1 px-4 py-3 text-sm',
        'text-gray-400 italic' => $isEmpty,
        'text-gray-700' => ! $isEmpty,
    ])>{{ $displayValue }}</div>
</div>
