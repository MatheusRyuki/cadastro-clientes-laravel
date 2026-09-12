<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesDrawerValidation;
use App\Http\Requests\Concerns\ValidatesCustomerFields;
use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    use HandlesDrawerValidation, ValidatesCustomerFields;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Customer $customer */
        $customer = $this->route('customer');

        return $this->customerFieldRules($customer);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->customerFieldAttributes();
    }

    /**
     * @return array<string, mixed>
     */
    protected function drawerFormViewData(): array
    {
        /** @var Customer $customer */
        $customer = $this->route('customer');

        return [
            'customer' => $customer,
            'action' => route('customers.update', $customer),
            'method' => 'PUT',
            'submitLabel' => __('customers.buttons.update'),
        ];
    }
}
