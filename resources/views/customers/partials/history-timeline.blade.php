@props(['customer', 'showDeleted' => false])

<div class="mt-6">
    <h3 class="mb-3 text-sm font-semibold text-gray-900">{{ __('customers.history.title') }}</h3>
    <ol class="relative border-l border-gray-200 pl-4">
        <li class="mb-4 ml-2">
            <span class="absolute -left-1.5 mt-1.5 size-3 rounded-full border-2 border-white bg-blue-600"></span>
            <p class="text-sm font-medium text-gray-900">{{ __('customers.history.created') }}</p>
            <time class="text-xs text-gray-500" datetime="{{ $customer->created_at?->toIso8601String() }}">
                {{ $customer->created_at?->locale('pt_BR')->isoFormat('LLL') }}
            </time>
        </li>
        @if ($customer->updated_at && ! $customer->created_at?->eq($customer->updated_at))
            <li class="mb-4 ml-2">
                <span class="absolute -left-1.5 mt-1.5 size-3 rounded-full border-2 border-white bg-gray-400"></span>
                <p class="text-sm font-medium text-gray-900">{{ __('customers.history.updated') }}</p>
                <time class="text-xs text-gray-500" datetime="{{ $customer->updated_at?->toIso8601String() }}">
                    {{ $customer->updated_at?->locale('pt_BR')->isoFormat('LLL') }}
                </time>
            </li>
        @endif
        @if ($showDeleted && $customer->trashed() && $customer->deleted_at)
            <li class="ml-2">
                <span class="absolute -left-1.5 mt-1.5 size-3 rounded-full border-2 border-white bg-red-500"></span>
                <p class="text-sm font-medium text-gray-900">{{ __('customers.history.deleted') }}</p>
                <time class="text-xs text-gray-500" datetime="{{ $customer->deleted_at?->toIso8601String() }}">
                    {{ $customer->deleted_at?->locale('pt_BR')->isoFormat('LLL') }}
                </time>
            </li>
        @endif
    </ol>
</div>
