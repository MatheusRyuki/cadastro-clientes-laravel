<div
    x-data="drawer"
    x-on:open-drawer.window="openDrawer($event.detail.url, $event.detail.title)"
    x-cloak
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-black/50"
        @click="close()"
        aria-hidden="true"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-lg flex-col border-l border-gray-200 bg-white shadow-xl"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="close()"
    >
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900" x-text="title"></h2>
            <button
                type="button"
                class="inline-flex size-10 cursor-pointer items-center justify-center rounded border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-50 active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2"
                :aria-label="@js(__('customers.dismiss'))"
                @click="close()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="relative flex-1 overflow-y-auto p-6">
            <div x-show="loading" class="flex items-center justify-center py-12">
                <svg class="size-8 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div x-show="!loading" x-ref="drawerContent"></div>
        </div>
    </div>
</div>
