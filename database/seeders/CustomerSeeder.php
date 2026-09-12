<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::factory(7)->create();

        $customers = [
            [
                'first_name' => 'Eusebia',
                'last_name' => 'Little',
                'phone' => '11829281212',
                'email' => 'lecubijev@mailinator.com',
                'ban' => '24521342142142134',
            ],
            [
                'first_name' => 'Amos',
                'last_name' => 'Gaines',
                'phone' => '11882422225',
                'email' => 'nejepu@mailinator.com',
                'ban' => '13244242243242343',
            ],
            [
                'first_name' => 'Amethyst',
                'last_name' => 'Morrow',
                'phone' => '11981086843',
                'email' => 'naxuvocy@mailinator.com',
                'ban' => '14242424432421424',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::query()->updateOrCreate(
                ['email' => $customer['email']],
                $customer,
            );
        }
    }
}
