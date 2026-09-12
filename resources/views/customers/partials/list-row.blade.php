@php
    $isDesktop = ($layout ?? 'desktop') === 'desktop';
    $isTrash = $trash ?? false;
    $refPrefix = ($layout ?? 'desktop') . ($isTrash ? 'Trash' : '') . $customer->id;
    $isSelected = ! $isTrash && (int) ($selected ?? null) === $customer->id;
    $listParams = array_filter([
        'search' => ($search ?? '') ?: null,
        'sort' => ($sort ?? 'newest') !== 'newest' ? ($sort ?? 'newest') : null,
    ]);
    $closeUrl = route('customers.index', $listParams);
    $rowUrl = $isTrash
        ? route('customers.show', $customer)
        : ($isSelected ? $closeUrl : route('customers.index', [...$listParams, 'selected' => $customer->id]));
@endphp

@if ($isDesktop)
<tr
    class="{{ $isTrash ? 'hover:bg-gray-50' : 'cursor-pointer transition hover:bg-gray-50' }} @if($isSelected) bg-blue-50 @endif"
    @if (! $isTrash) onclick="window.location='{{ $rowUrl }}'" @endif
>
    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $customer->relative_created_at }}</td>
    <td class="whitespace-nowrap px-6 py-4">
        <div class="flex items-center gap-3">
            <x-profile-avatar
                :src="$customer->imageUrl()"
                :alt="$customer->full_name"
                :size="40"
                variant="form"
            />
            <span class="text-sm font-medium text-gray-900">{{ $customer->full_name }}</span>
        </div>
    </td>
    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $customer->formatted_phone }}</td>
    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $customer->email }}</td>
    <td class="whitespace-nowrap px-6 py-4" @if (! $isTrash) onclick="event.stopPropagation()" @endif>
        @include('customers.partials.row-actions', [
            'customer' => $customer,
            'refPrefix' => $refPrefix,
            'trash' => $isTrash,
            'search' => $search ?? '',
            'sort' => $sort ?? 'newest',
        ])
    </td>
</tr>
@else
<div
    class="border-b border-gray-200 p-4 {{ $isTrash ? '' : 'cursor-pointer transition hover:bg-gray-50' }} @if($isSelected) bg-blue-50 @endif"
    @if (! $isTrash) onclick="window.location='{{ $rowUrl }}'" @endif
>
    <div class="flex items-start gap-3">
        <x-profile-avatar
            :src="$customer->imageUrl()"
            :alt="$customer->full_name"
            :size="48"
            variant="form"
        />
        <div class="min-w-0 flex-1">
            <div class="font-medium text-gray-900">{{ $customer->full_name }}</div>
            <div class="mt-0.5 text-sm text-gray-500">{{ $customer->email }}</div>
            <div class="mt-0.5 text-sm text-gray-500">{{ $customer->formatted_phone }}</div>
            @if (! $isTrash)
                <div class="mt-1 text-xs text-gray-400">{{ $customer->relative_created_at }}</div>
            @endif
        </div>
    </div>
    <div class="mt-3 flex items-center gap-2" @if (! $isTrash) onclick="event.stopPropagation()" @endif>
        @include('customers.partials.row-actions', [
            'customer' => $customer,
            'refPrefix' => $refPrefix,
            'trash' => $isTrash,
            'search' => $search ?? '',
            'sort' => $sort ?? 'newest',
        ])
    </div>
</div>
@endif
