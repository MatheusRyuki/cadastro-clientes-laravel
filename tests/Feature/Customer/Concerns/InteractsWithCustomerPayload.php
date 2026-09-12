<?php

namespace Tests\Feature\Customer\Concerns;

use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;

trait InteractsWithCustomerPayload
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validCustomerPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'João',
            'last_name' => 'Silva',
            'email' => 'joao@example.com',
            'phone' => '11987654321',
            'ban' => '1234567890123456',
        ], $overrides);
    }

    protected function fakeCustomerImage(string $name = 'photo.jpg'): File
    {
        return UploadedFile::fake()->image($name);
    }
}
