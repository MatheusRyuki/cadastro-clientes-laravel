@if ($errors->any())
    <div role="alert" class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="mb-2 font-medium">{{ __('customers.errors.summary_title') }}</p>
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->keys() as $field)
                <li>
                    <a
                        href="#{{ $field }}"
                        class="underline transition hover:text-red-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
                    >
                        {{ $errors->first($field) }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
