@use('Illuminate\Support\Js')

<div class="flex items-center gap-2">
    @if ($trash)
        <form x-ref="restoreForm{{ $refPrefix }}" method="POST" action="{{ route('customers.restore', $customer) }}" class="inline">
            @csrf
            @method('PATCH')
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <x-icon-button
                type="button"
                :label="__('customers.actions.restore')"
                icon="restore"
                @click.prevent="$dispatch('open-confirm-modal', {
                    title: {{ Js::from(__('customers.confirm.restore')) }},
                    message: {{ Js::from(__('customers.confirm.restore_description')) }},
                    confirmLabel: {{ Js::from(__('customers.buttons.restore')) }},
                    cancelLabel: {{ Js::from(__('customers.buttons.cancel')) }},
                    variant: 'primary',
                    form: $refs.restoreForm{{ $refPrefix }}
                })"
            />
        </form>
        <form x-ref="forceForm{{ $refPrefix }}" method="POST" action="{{ route('customers.force-destroy', $customer) }}" class="inline">
            @csrf
            @method('DELETE')
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <x-icon-button
                type="button"
                :label="__('customers.actions.force_delete')"
                icon="trash-x"
                variant="destructive"
                @click.prevent="$dispatch('open-confirm-modal', {
                    title: {{ Js::from(__('customers.confirm.force_delete')) }},
                    message: {{ Js::from(__('customers.confirm.force_delete_description')) }},
                    confirmLabel: {{ Js::from(__('customers.buttons.force_delete')) }},
                    cancelLabel: {{ Js::from(__('customers.buttons.cancel')) }},
                    variant: 'destructive',
                    form: $refs.forceForm{{ $refPrefix }}
                })"
            />
        </form>
    @else
        <x-icon-button href="{{ route('customers.show', $customer) }}" :label="__('customers.actions.view')" icon="view" />
        <x-icon-button
            href="#"
            :label="__('customers.actions.edit')"
            icon="edit"
            @click.prevent="$dispatch('open-drawer', { url: '{{ route('customers.edit', ['customer' => $customer, 'drawer' => 1]) }}', title: {{ Js::from(__('customers.edit_title')) }} })"
        />
        <form x-ref="deleteForm{{ $refPrefix }}" method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline">
            @csrf
            @method('DELETE')
            <x-icon-button
                type="button"
                :label="__('customers.actions.delete')"
                icon="delete"
                variant="destructive"
                @click.prevent="$dispatch('open-confirm-modal', {
                    title: {{ Js::from(__('customers.confirm.delete')) }},
                    message: {{ Js::from(__('customers.confirm.delete_description')) }},
                    confirmLabel: {{ Js::from(__('customers.buttons.delete')) }},
                    cancelLabel: {{ Js::from(__('customers.buttons.cancel')) }},
                    variant: 'destructive',
                    form: $refs.deleteForm{{ $refPrefix }}
                })"
            />
        </form>
    @endif
</div>
