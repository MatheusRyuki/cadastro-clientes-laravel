<div x-data="commandPalette" x-cloak>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 p-4 pt-[15vh]"
        @keydown.escape.window="open = false"
        @click.self="open = false"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="w-full max-w-lg overflow-hidden rounded-lg border border-gray-200 bg-white shadow-2xl"
            x-trap.noscroll="open"
            role="dialog"
            aria-modal="true"
            aria-label="{{ __('customers.command_palette.title') }}"
        >
            <div class="border-b border-gray-200 px-4 py-3">
                <input
                    x-ref="searchInput"
                    type="search"
                    x-model="query"
                    @input.debounce.300ms="search()"
                    @keydown="onKeydown($event)"
                    placeholder="{{ __('customers.command_palette.placeholder') }}"
                    class="w-full border-0 bg-transparent text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0"
                >
            </div>

            <ul class="max-h-64 overflow-y-auto py-2" role="listbox">
                <template x-if="loading">
                    <li class="px-4 py-3 text-sm text-gray-500">{{ __('customers.search') }}…</li>
                </template>
                <template x-if="!loading && query.length >= 2 && results.length === 0">
                    <li class="px-4 py-3 text-sm text-gray-500">{{ __('customers.command_palette.no_results') }}</li>
                </template>
                <template x-for="(result, index) in results" :key="result.id">
                    <li>
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center gap-3 px-4 py-2.5 text-left text-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500"
                            :class="index === selectedIndex ? 'bg-blue-50 text-blue-900' : 'text-gray-700 hover:bg-gray-50'"
                            @click="selectResult(result)"
                            @mouseenter="selectedIndex = index"
                        >
                            <span class="font-medium" x-text="result.name"></span>
                            <span class="truncate text-gray-500" x-text="result.email"></span>
                        </button>
                    </li>
                </template>
            </ul>

            <div class="border-t border-gray-200 px-4 py-2 text-xs text-gray-500">
                {{ __('customers.command_palette.hint') }}
            </div>
        </div>
    </div>
</div>
