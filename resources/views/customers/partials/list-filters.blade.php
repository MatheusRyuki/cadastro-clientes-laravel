@php
    $isSplitView = ! empty($selected);
@endphp

<form
    method="GET"
    action="{{ $action }}"
    @class([
        'flex w-full min-w-0 flex-col gap-2',
        'sm:flex-row sm:flex-wrap sm:items-center sm:gap-3' => ! $isSplitView,
        'md:flex-col md:items-stretch lg:flex-row lg:flex-wrap lg:items-center lg:gap-3' => $isSplitView,
    ])
>
    @if ($isSplitView)
        <input type="hidden" name="selected" value="{{ $selected }}">
    @endif

    <div @class([
        'flex min-w-0 w-full flex-col gap-1.5',
        'sm:flex-1 sm:flex-row sm:items-center sm:gap-3' => ! $isSplitView,
        'md:flex-1 lg:flex-row lg:items-center lg:gap-3' => $isSplitView,
    ])>
        <div class="flex min-w-0 w-full items-stretch">
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="{{ __('customers.search_placeholder') }}"
                aria-describedby="search-hint"
                class="min-w-0 flex-1 rounded-l border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus-visible:ring-2 focus-visible:ring-blue-500"
            >
            <button
                type="submit"
                class="shrink-0 cursor-pointer rounded-r border border-l-0 border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2"
            >
                {{ __('customers.search') }}
            </button>
        </div>
        <p
            id="search-hint"
            @class([
                'text-sm text-gray-500',
                'sm:shrink-0' => ! $isSplitView,
                'lg:shrink-0' => $isSplitView,
            ])
        >{{ __('customers.search_hint') }}</p>
    </div>

    <select
        name="sort"
        onchange="this.form.submit()"
        @class([
            'cursor-pointer rounded border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus-visible:ring-2 focus-visible:ring-blue-500',
            'w-full sm:w-auto sm:shrink-0' => ! $isSplitView,
            'w-full lg:w-auto lg:shrink-0' => $isSplitView,
        ])
    >
        <option value="newest" @selected($sort === 'newest')>{{ __('customers.sort.newest') }}</option>
        <option value="oldest" @selected($sort === 'oldest')>{{ __('customers.sort.oldest') }}</option>
    </select>
</form>
