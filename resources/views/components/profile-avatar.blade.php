@props([
    'src' => null,
    'alt',
    'size' => 112,
    'variant' => 'header',
])

@php
    $sizeClass = match ($size) {
        40 => 'size-10',
        48 => 'size-12',
        80 => 'size-20',
        96 => 'size-24',
        default => 'size-28',
    };
    $borderClass = $variant === 'form'
        ? 'border border-gray-200'
        : 'border-2 border-white';
    $placeholderBg = $variant === 'form' ? 'bg-gray-200' : 'bg-gray-500';
    $iconClass = match ($size) {
        40 => 'size-5 text-gray-400',
        48 => 'size-6 text-gray-400',
        96 => 'size-10 text-gray-400',
        default => 'size-12 text-gray-300',
    };
@endphp

<div {{ $attributes->merge(['class' => "{$sizeClass} shrink-0 overflow-hidden rounded-lg {$borderClass}"]) }}>
    @if ($src)
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            width="{{ $size }}"
            height="{{ $size }}"
            class="block size-full object-cover object-center"
            loading="lazy"
            decoding="async"
        >
    @else
        <div class="flex size-full items-center justify-center {{ $placeholderBg }}" role="img" aria-label="{{ $alt }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.375 3.375 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </div>
    @endif
</div>
