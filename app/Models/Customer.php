<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable(['first_name', 'last_name', 'phone', 'email', 'ban', 'about', 'image'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, SoftDeletes;

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function getFormattedPhoneAttribute(): string
    {
        $digits = preg_replace('/\D/', '', $this->phone) ?? '';

        if (strlen($digits) === 11) {
            return sprintf(
                '+55 (%s) %s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 5),
                substr($digits, 7, 4),
            );
        }

        if (strlen($digits) === 10) {
            return sprintf(
                '+55 (%s) %s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 4),
                substr($digits, 6, 4),
            );
        }

        return $this->phone ?? '';
    }

    public function getRelativeCreatedAtAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->locale('pt_BR')->diffForHumans()
            : '';
    }

    public function setPhoneAttribute(?string $value): void
    {
        if ($value === null) {
            $this->attributes['phone'] = null;

            return;
        }

        $digits = preg_replace('/\D/', '', $value) ?? '';

        if (str_starts_with($digits, '55') && strlen($digits) > 11) {
            $digits = substr($digits, 2);
        }

        $this->attributes['phone'] = $digits;
    }
}
