<?php

namespace App\Http\Requests\Concerns;

use App\Models\Customer;
use Illuminate\Validation\Rule;

trait ValidatesCustomerFields
{
    /**
     * @return array<string, mixed>
     */
    protected function customerFieldRules(?Customer $customer = null): array
    {
        $emailRules = ['required', 'email', 'max:255'];

        if ($customer !== null) {
            $emailRules[] = Rule::unique('customers', 'email')->ignore($customer)->withoutTrashed();
        } else {
            $emailRules[] = Rule::unique('customers', 'email')->withoutTrashed();
        }

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'phone' => ['required', 'string', 'max:20'],
            'ban' => ['required', 'numeric'],
            'about' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function customerFieldAttributes(): array
    {
        return [
            'first_name' => __('customers.fields.first_name'),
            'last_name' => __('customers.fields.last_name'),
            'email' => __('customers.fields.email'),
            'phone' => __('customers.fields.phone'),
            'ban' => __('customers.fields.ban'),
            'about' => __('customers.fields.about'),
            'image' => __('customers.fields.image'),
        ];
    }
}
