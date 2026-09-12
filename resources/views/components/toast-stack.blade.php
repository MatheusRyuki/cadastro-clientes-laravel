<div
    x-data
    x-init="
        document.querySelectorAll('[data-flash-message]').forEach((el) => {
            $store.toasts.push(el.dataset.flashMessage, el.dataset.flashType ?? 'success');
            el.remove();
        });
    "
    class="pointer-events-none fixed bottom-4 right-4 z-50 flex w-full max-w-sm flex-col items-end gap-2 px-4 sm:max-w-md"
    aria-live="polite"
    aria-atomic="false"
>
    @if (session('success'))
        <div data-flash-message="{{ session('success') }}" data-flash-type="success" class="hidden" aria-hidden="true"></div>
    @endif
    @if (session('error'))
        <div data-flash-message="{{ session('error') }}" data-flash-type="error" class="hidden" aria-hidden="true"></div>
    @endif

    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div
            class="pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-lg border px-4 py-3 text-sm shadow-lg transition"
            role="status"
            :class="toast.type === 'error'
                ? 'border-red-200 bg-red-50 text-red-800'
                : 'border-green-200 bg-green-50 text-green-800'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
        >
            <p class="flex-1" x-text="toast.message"></p>
            <button
                type="button"
                class="shrink-0 rounded p-0.5 transition hover:bg-black/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-current"
                :aria-label="@js(__('customers.dismiss'))"
                @click="$store.toasts.dismiss(toast.id)"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>
