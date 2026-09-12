<div
    x-data="confirmModal"
    x-on:open-confirm-modal.window="show($event.detail)"
    x-cloak
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="$id('modal-title')"
        @keydown.escape.window="cancel()"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-md rounded-lg border border-gray-200 bg-white p-6 shadow-xl"
            x-trap.noscroll="open"
            @click.outside="cancel()"
        >
            <h2 :id="$id('modal-title')" class="text-lg font-semibold text-gray-900" x-text="title"></h2>
            <p class="mt-2 text-sm text-gray-600" x-text="message"></p>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center justify-center gap-2 rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2"
                    @click="cancel()"
                    x-text="cancelLabel || @js(__('customers.buttons.cancel'))"
                ></button>
                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center justify-center gap-2 rounded px-4 py-2 text-sm font-medium text-white transition active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    :class="variant === 'destructive'
                        ? 'bg-red-600 hover:bg-red-700 focus-visible:ring-red-500'
                        : 'bg-blue-600 hover:bg-blue-700 focus-visible:ring-blue-500'"
                    @click="confirm()"
                    x-text="confirmLabel || @js(__('customers.buttons.confirm'))"
                ></button>
            </div>
        </div>
    </div>
</div>
