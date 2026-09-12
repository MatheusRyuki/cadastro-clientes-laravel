@props(['items'])

<nav aria-label="Breadcrumb" class="mb-4">
    <ol class="flex flex-wrap items-center gap-1 text-sm text-gray-500">
        @foreach ($items as $index => $item)
            <li class="flex items-center gap-1">
                @if ($index > 0)
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                @endif

                @if (isset($item['url']) && ! ($item['current'] ?? false))
                    <a
                        href="{{ $item['url'] }}"
                        class="rounded transition hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                    >
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-medium text-gray-900" @if ($item['current'] ?? false) aria-current="page" @endif>
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
