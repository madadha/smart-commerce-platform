<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'country_id',
        'customer_type',
        'status',
        'first_name',
        'last_name',
        'email',
        'phone',
        'whatsapp',
        'identity_number',
        'birth_date',
        'company_name',
        'tax_number',
        'city',
        'area',
        'street',
        'building',
        'apartment',
        'postal_code',
        'address_notes',
        'internal_notes',
        'accepts_marketing',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'customer_type' => CustomerType::class,
        'status' => CustomerStatus::class,
        'birth_date' => 'date',
        'accepts_marketing' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', CustomerStatus::Active->value)
            ->where('is_active', true);
    }

    public function scopeResellers($query)
    {
        return $query->where('customer_type', CustomerType::Reseller->value);
    }

    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . ($this->last_name ?? ''));
    }

    public function getDisplayName(): string
    {
        if ($this->company_name) {
            return $this->company_name . ' - ' . $this->getFullName();
        }

        return $this->getFullName();
    }

    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->country?->getName('ar'),
            $this->city,
            $this->area,
            $this->street,
            $this->building ? 'بناية ' . $this->building : null,
            $this->apartment ? 'شقة ' . $this->apartment : null,
        ]);

        return implode('، ', $parts);
    }

    public function isReseller(): bool
    {
        return $this->customer_type === CustomerType::Reseller;
    }

    public function isBlocked(): bool
    {
        return $this->status === CustomerStatus::Blocked;
    }
}