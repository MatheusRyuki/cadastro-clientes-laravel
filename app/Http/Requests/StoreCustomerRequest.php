<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HandlesDrawerValidation;
use App\Http\Requests\Concerns\ValidatesCustomerFields;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
        return $this->customerFieldRules();
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
        return [
            'action' => route('customers.store'),
            'method' => 'POST',
            'submitLabel' => __('customers.buttons.create'),
        ];
    }
}
