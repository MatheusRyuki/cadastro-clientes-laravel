<?php

namespace Tests\Unit;

use App\Models\Customer;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    public function test_full_name_accessor_concatenates_first_and_last_name(): void
    {
        $customer = new Customer([
            'first_name' => 'João',
            'last_name' => 'Silva',
        ]);

        $this->assertSame('João Silva', $customer->full_name);
    }

    public function test_full_name_accessor_trims_leading_and_trailing_whitespace(): void
    {
        $customer = new Customer([
            'first_name' => ' Ana',
            'last_name' => 'Costa ',
        ]);

        $this->assertSame('Ana Costa', $customer->full_name);
    }

    public function test_relative_created_at_accessor_returns_portuguese_human_diff(): void
    {
        Carbon::setTestNow(Carbon::parse('2024-01-01 13:00:00'));

        $customer = new class extends Customer
        {
            public function __construct() {}

            public function getCreatedAtAttribute($value): Carbon
            {
                return Carbon::parse('2024-01-01 12:00:00');
            }
        };

        $this->assertSame('há 1 hora', $customer->relative_created_at);

        Carbon::setTestNow();
    }

    public function test_relative_created_at_accessor_returns_empty_string_without_created_at(): void
    {
        $customer = new class extends Customer
        {
            public function __construct() {}
        };

        $this->assertSame('', $customer->relative_created_at);
    }

    public function test_formatted_phone_accessor_formats_eleven_digit_brazilian_number(): void
    {
        $customer = new Customer(['phone' => '11987654321']);

        $this->assertSame('+55 (11) 98765-4321', $customer->formatted_phone);
    }

    public function test_formatted_phone_accessor_formats_ten_digit_brazilian_number(): void
    {
        $customer = new Customer(['phone' => '1134567890']);

        $this->assertSame('+55 (11) 3456-7890', $customer->formatted_phone);
    }

    public function test_formatted_phone_accessor_returns_original_value_for_unexpected_length(): void
    {
        $customer = new Customer(['phone' => '123']);

        $this->assertSame('123', $customer->formatted_phone);
    }

    public function test_phone_mutator_strips_formatting_when_setting(): void
    {
        $customer = new Customer;
        $customer->phone = '+55 (11) 98765-4321';

        $this->assertSame('11987654321', $customer->getAttributes()['phone']);
    }

    public function test_phone_mutator_strips_country_code_prefix(): void
    {
        $customer = new Customer;
        $customer->phone = '5511987654321';

        $this->assertSame('11987654321', $customer->getAttributes()['phone']);
    }

    public function test_phone_mutator_sets_null_when_value_is_null(): void
    {
        $customer = new Customer;
        $customer->phone = null;

        $this->assertNull($customer->getAttributes()['phone']);
    }
}
