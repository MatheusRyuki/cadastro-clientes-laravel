@use('Illuminate\Support\Js')

@props([
    'message',
    'description' => null,
    'ctaLabel' => null,
    'ctaUrl' => null,
    'ctaDrawer' => null,
    'secondaryLabel' => null,
    'secondaryUrl' => null,
])

<div class="px-6 py-16 text-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto size-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
    </svg>
    <p class="mt-4 text-sm font-medium text-gray-900">{{ $message }}</p>
    @if ($description)
        <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
    @endif
    @if ($ctaLabel && ($ctaUrl || $ctaDrawer))
        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            @if ($ctaDrawer)
                <x-btn
                    href="#"
                    @click.prevent="$dispatch('open-drawer', { url: '{{ $ctaDrawer }}', title: {{ Js::from(__('customers.create_title')) }} })"
                >
                    {{ $ctaLabel }}
                </x-btn>
            @else
                <x-btn :href="$ctaUrl">{{ $ctaLabel }}</x-btn>
            @endif
            @if ($secondaryLabel && $secondaryUrl)
                <x-btn variant="secondary" :href="$secondaryUrl">{{ $secondaryLabel }}</x-btn>
            @endif
        </div>
    @endif
</div>
