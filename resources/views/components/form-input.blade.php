@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
])

<div>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-gray-700">{{ $label }}</label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'w-full rounded border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus-visible:ring-2 focus-visible:ring-blue-500' . ($errors->has($name) ? ' border-red-500 focus:border-red-500 focus:ring-red-500' : '')]) }}
    >
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
