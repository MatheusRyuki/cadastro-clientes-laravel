<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredCustomersQuery($request);
        $selectedCustomer = null;

        if ($request->filled('selected')) {
            $selectedCustomer = (clone $query)
                ->where('id', $request->integer('selected'))
                ->first();
        }

        return view('customers.index', [
            ...$this->filteredCustomers($request),
            'selectedCustomer' => $selectedCustomer,
        ]);
    }

    public function trash(Request $request): View
    {
        return view('customers.trash', $this->filteredCustomers($request, trashed: true));
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->string('q')->trim()->toString();

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $customers = $this->applySearchFilter(Customer::query(), $query)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'email']);

        return response()->json(
            $customers->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->full_name,
                'email' => $customer->email,
                'url' => route('customers.index', ['selected' => $customer->id]),
            ]),
        );
    }

    public function create(Request $request): View
    {
        if ($request->boolean('drawer')) {
            return view('customers.partials.drawer-form', [
                'action' => route('customers.store'),
                'method' => 'POST',
                'submitLabel' => __('customers.buttons.create'),
            ]);
        }

        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::query()->create($this->validatedData($request));

        return $this->redirectAfterSave($customer, $request, 'created');
    }

    public function show(Customer $customer): View
    {
        return view('customers.show', compact('customer'));
    }

    public function edit(Request $request, Customer $customer): View
    {
        if ($request->boolean('drawer')) {
            return view('customers.partials.drawer-form', [
                'customer' => $customer,
                'action' => route('customers.update', $customer),
                'method' => 'PUT',
                'submitLabel' => __('customers.buttons.update'),
            ]);
        }

        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validatedData($request, $customer));

        return $this->redirectAfterSave($customer, $request, 'updated');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', __('customers.flash.deleted'));
    }

    public function restore(int $customer): RedirectResponse
    {
        $customer = Customer::onlyTrashed()->find($customer);

        if ($customer === null) {
            return $this->redirectToTrash('error', 'customers.flash.restore_failed');
        }

        if ($this->emailTakenByActiveCustomer((string) $customer->email)) {
            return $this->redirectToTrash('error', 'customers.flash.restore_email_conflict');
        }

        $customer->restore();

        return $this->redirectToTrash('success', 'customers.flash.restored');
    }

    public function forceDestroy(int $customer): RedirectResponse
    {
        $customer = Customer::onlyTrashed()->findOrFail($customer);

        $this->deleteCustomerImage($customer);
        $customer->forceDelete();

        return $this->redirectToTrash('success', 'customers.flash.force_deleted');
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        $ids = array_filter($request->input('ids', []));

        if ($ids === []) {
            return $this->redirectToTrash('error', 'customers.flash.bulk_none_selected');
        }

        $candidates = Customer::onlyTrashed()->whereIn('id', $ids)->get();

        if ($candidates->isEmpty()) {
            return $this->redirectToTrash('error', 'customers.flash.bulk_none_matched');
        }

        $restored = 0;

        foreach ($candidates as $customer) {
            if ($this->emailTakenByActiveCustomer((string) $customer->email)) {
                continue;
            }

            $customer->restore();
            $restored++;
        }

        if ($restored === 0) {
            return $this->redirectToTrash('error', 'customers.flash.restore_email_conflict');
        }

        return $this->redirectToTrash('success', 'customers.flash.bulk_restored', ['count' => $restored]);
    }

    public function bulkForceDestroy(Request $request): RedirectResponse
    {
        $ids = array_filter($request->input('ids', []));

        if ($ids === []) {
            return $this->redirectToTrash('error', 'customers.flash.bulk_none_selected');
        }

        $customers = Customer::onlyTrashed()->whereIn('id', $ids)->get();

        if ($customers->isEmpty()) {
            return $this->redirectToTrash('error', 'customers.flash.bulk_none_matched');
        }

        foreach ($customers as $customer) {
            $this->deleteCustomerImage($customer);
            $customer->forceDelete();
        }

        return $this->redirectToTrash('success', 'customers.flash.bulk_force_deleted', ['count' => $customers->count()]);
    }

    /**
     * @return array{customers: LengthAwarePaginator, search: string, sort: string}
     */
    private function filteredCustomers(Request $request, bool $trashed = false): array
    {
        $search = $request->string('search')->trim()->toString();
        $sort = $request->string('sort', 'newest')->toString();

        return [
            'customers' => $this->filteredCustomersQuery($request, $trashed)
                ->paginate(15)
                ->withQueryString(),
            'search' => $search,
            'sort' => $sort,
        ];
    }

    private function filteredCustomersQuery(Request $request, bool $trashed = false): Builder
    {
        $search = $request->string('search')->trim()->toString();
        $sort = $request->string('sort', 'newest')->toString();

        $query = $trashed
            ? Customer::onlyTrashed()
            : Customer::query();

        if ($search !== '') {
            $this->applySearchFilter($query, $search);
        }

        return $query->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc');
    }

    private function emailTakenByActiveCustomer(string $email): bool
    {
        return Customer::query()->where('email', $email)->exists();
    }

    private function applySearchFilter(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $builder) use ($term) {
            $builder
                ->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('ban', 'like', "%{$term}%");
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(StoreCustomerRequest|UpdateCustomerRequest $request, ?Customer $customer = null): array
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $this->deleteCustomerImage($customer);
            $data['image'] = $request->file('image')->store('customers', 'public');
        }

        return $data;
    }

    private function deleteCustomerImage(?Customer $customer): void
    {
        if ($customer?->image) {
            Storage::disk('public')->delete($customer->image);
        }
    }

    private function redirectAfterSave(Customer $customer, StoreCustomerRequest|UpdateCustomerRequest $request, string $flashKey): RedirectResponse
    {
        $message = __("customers.flash.{$flashKey}");

        if ($request->boolean('from_drawer')) {
            return redirect()
                ->route('customers.index', ['selected' => $customer->id])
                ->with('success', $message);
        }

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', $message);
    }

    /**
     * @param  array<string, int|string>  $replace
     */
    private function redirectToTrash(string $type, string $messageKey, array $replace = []): RedirectResponse
    {
        return redirect()
            ->route('customers.trash', $this->trashRedirectParams())
            ->with($type, __($messageKey, $replace));
    }

    /**
     * @return array<string, string>
     */
    private function trashRedirectParams(): array
    {
        return array_filter([
            'search' => request()->string('search')->trim()->toString(),
            'sort' => request()->string('sort')->toString() !== 'newest'
                ? request()->string('sort')->toString()
                : null,
        ]);
    }
}
