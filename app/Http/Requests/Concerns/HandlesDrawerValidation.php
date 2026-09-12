<?php

namespace App\Http\Requests\Concerns;

use App\Models\Customer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

trait HandlesDrawerValidation
{
    /** @var list<string> */
    private const DRAWER_FORM_FIELDS = ['first_name', 'last_name', 'email', 'phone', 'ban', 'about'];

    protected function failedValidation(Validator $validator): void
    {
        if ($this->boolean('from_drawer')) {
            $this->session()->flashInput($this->except($this->dontFlash));

            View::share(
                'errors',
                (new ViewErrorBag)->put($this->errorBag, $validator->errors())
            );

            throw new HttpResponseException(
                response(
                    view($this->drawerFormView(), $this->drawerFormViewDataWithInput()),
                    422
                )
            );
        }

        parent::failedValidation($validator);
    }

    protected function drawerFormView(): string
    {
        return 'customers.partials.drawer-form';
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function drawerFormViewData(): array;

    /**
     * @return array<string, mixed>
     */
    protected function drawerFormViewDataWithInput(): array
    {
        $viewData = $this->drawerFormViewData();

        if (isset($viewData['customer']) && $viewData['customer'] instanceof Customer) {
            $customer = $viewData['customer']->replicate();
            $customer->fill($this->only(self::DRAWER_FORM_FIELDS));
        } else {
            $customer = new Customer($this->only(self::DRAWER_FORM_FIELDS));
        }

        $viewData['customer'] = $customer;

        return $viewData;
    }
}
