@use('Illuminate\Support\Js')

<div class="flex h-full flex-col">
    <div class="flex items-center gap-4 border-b border-gray-200 bg-gray-600 px-6 py-5">
        <x-profile-avatar
            :src="$customer->image ? asset('storage/' . $customer->image) : null"
            :alt="$customer->full_name"
            :size="80"
            variant="header"
        />
        <div class="min-w-0 flex-1">
            <h2 class="truncate text-xl font-semibold text-white">{{ $customer->full_name }}</h2>
            <p class="truncate text-sm text-gray-300">{{ $customer->email }}</p>
            <p class="truncate text-sm text-gray-300">{{ $customer->formatted_phone }}</p>
        </div>
        <x-icon-button
            :href="$closeUrl"
            :label="__('customers.dismiss')"
            icon="close"
            class="shrink-0"
        />
    </div>

    <div class="flex-1 overflow-y-auto px-6 py-6">
        <div class="overflow-hidden rounded border border-gray-200">
            <x-detail-row :label="__('customers.fields.ban')" :value="$customer->ban" />
            <x-detail-row :label="__('customers.fields.about')" :value="$customer->about" />
        </div>

        @include('customers.partials.history-timeline', ['customer' => $customer])
    </div>

    <div class="flex flex-wrap items-center gap-3 border-t border-gray-200 px-6 py-4">
        <x-btn
            href="#"
            @click.prevent="$dispatch('open-drawer', { url: '{{ route('customers.edit', ['customer' => $customer, 'drawer' => 1]) }}', title: {{ Js::from(__('customers.edit_title')) }} })"
        >
            {{ __('customers.buttons.edit') }}
        </x-btn>

        <form
            x-ref="panelDeleteForm"
            method="POST"
            action="{{ route('customers.destroy', $customer) }}"
            class="inline"
        >
            @csrf
            @method('DELETE')
            <x-btn
                variant="destructive"
                type="button"
                @click.prevent="$dispatch('open-confirm-modal', {
                    title: {{ Js::from(__('customers.confirm.delete')) }},
                    message: {{ Js::from(__('customers.confirm.delete_description')) }},
                    confirmLabel: {{ Js::from(__('customers.buttons.delete')) }},
                    cancelLabel: {{ Js::from(__('customers.buttons.cancel')) }},
                    variant: 'destructive',
                    form: $refs.panelDeleteForm
                })"
            >
                {{ __('customers.buttons.delete') }}
            </x-btn>
        </form>

        <x-btn variant="secondary" href="{{ route('customers.show', $customer) }}">
            {{ __('customers.actions.view') }}
        </x-btn>
    </div>
</div>
