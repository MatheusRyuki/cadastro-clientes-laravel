@props([
    'active' => 'customers',
    'trashCount' => 0,
])

<div class="mb-6 border-b border-gray-200">
    <nav class="-mb-px flex gap-6" aria-label="{{ __('customers.title') }}">
        <a
            href="{{ route('customers.index') }}"
            @class([
                'inline-flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2',
                'border-blue-600 text-blue-600' => $active === 'customers',
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => $active !== 'customers',
            ])
            @if ($active === 'customers') aria-current="page" @endif
        >
            {{ __('customers.title') }}
        </a>

        <a
            href="{{ route('customers.trash') }}"
            @class([
                'inline-flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2',
                'border-blue-600 text-blue-600' => $active === 'trash',
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' => $active !== 'trash',
            ])
            @if ($active === 'trash') aria-current="page" @endif
        >
            {{ __('customers.trash') }}
            @if ($trashCount > 0)
                <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-amber-100 px-1.5 py-0.5 text-xs font-semibold text-amber-800">
                    {{ $trashCount }}
                </span>
            @endif
        </a>
    </nav>
</div>
