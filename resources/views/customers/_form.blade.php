@props([
    'customer' => null,
    'submitLabel' => null,
])

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <x-form-input :label="__('customers.fields.first_name')" name="first_name" :value="$customer?->first_name" />
    <x-form-input :label="__('customers.fields.last_name')" name="last_name" :value="$customer?->last_name" />
</div>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <x-form-input :label="__('customers.fields.email')" name="email" type="email" :value="$customer?->email" />
    <div x-data="phoneInput">
        <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('customers.fields.phone') }}</label>
        <input
            type="tel"
            name="phone"
            id="phone"
            x-ref="phone"
            value="{{ old('phone', $customer?->formatted_phone) }}"
            placeholder="+55 (11) 98765-4321"
            @input="formatPhone($event)"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus-visible:ring-2 focus-visible:ring-blue-500 @error('phone') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
        >
        @error('phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="w-full">
    <x-form-input :label="__('customers.fields.ban')" name="ban" :value="$customer?->ban" />
</div>

<div class="w-full">
    <x-form-textarea :label="__('customers.fields.about')" name="about" :value="$customer?->about" />
</div>

<div class="w-full" x-data="imagePreview">
    <label for="image" class="mb-1 block text-sm font-medium text-gray-700">{{ __('customers.fields.image_optional') }}</label>

    @if ($customer?->image)
        <div class="mb-3" x-show="!preview">
            <x-profile-avatar
                :src="asset('storage/' . $customer->image)"
                :alt="__('customers.image.current_alt', ['name' => $customer->full_name])"
                :size="96"
                variant="form"
            />
            <p class="mt-1 text-xs text-gray-500">{{ __('customers.image.current') }}</p>
        </div>
    @endif

    <div x-show="preview" x-cloak class="mb-3">
        <img :src="preview" alt="{{ __('customers.image.preview_alt') }}" class="size-24 rounded-lg border border-gray-200 object-cover">
    </div>

    <input
        type="file"
        name="image"
        id="image"
        accept="image/*"
        @change="handleChange($event)"
        class="w-full rounded border border-gray-300 px-3 py-2 text-sm text-gray-900 file:mr-4 file:cursor-pointer file:rounded file:border-0 file:bg-gray-100 file:px-4 file:py-1.5 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus-visible:ring-2 focus-visible:ring-blue-500 @error('image') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
    >
    @error('image')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="mt-8 flex flex-wrap items-center gap-3 pt-2">
    <button
        type="submit"
        :disabled="submitting"
        class="inline-flex cursor-pointer items-center gap-2 rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <svg x-show="submitting" x-cloak class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span x-text="submitting ? @js(__('customers.buttons.saving')) : @js($submitLabel ?? __('customers.buttons.create'))"></span>
    </button>

    <x-btn variant="secondary" :href="route('customers.index')">{{ __('customers.back') }}</x-btn>
</div>
