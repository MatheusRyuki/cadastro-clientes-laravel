@props([
    'customer' => null,
    'action' => '',
    'method' => 'POST',
    'submitLabel',
])

@if ($errors->any())
    @include('components.error-summary')
@endif

<form
    method="POST"
    action="{{ $action }}"
    enctype="multipart/form-data"
    data-drawer-form
    x-data="formSubmit"
    @submit="handleSubmit($event)"
    class="space-y-6"
>
    @csrf
    <input type="hidden" name="from_drawer" value="1">
    @if ($method !== 'POST')
        @method($method)
    @endif

    @include('customers._form', [
        'customer' => $customer,
        'submitLabel' => $submitLabel,
    ])
</form>
