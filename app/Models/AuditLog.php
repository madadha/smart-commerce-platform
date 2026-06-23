<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'subject_type',
        'subject_id',
        'route',
        'method',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getSubjectLabelAttribute(): string
    {
        $subject = $this->subject;

        if (! $subject) {
            return $this->subject_type
                ? class_basename($this->subject_type).' #'.($this->subject_id ?? 'n/a')
                : 'System';
        }

        foreach ([
            'order_number',
            'payment_number',
            'shipment_number',
            'sku',
            'name',
            'title',
            'email',
            'provider',
            'key',
        ] as $attribute) {
            $value = $subject->{$attribute} ?? null;

            if (filled($value)) {
                return class_basename($subject::class).' '.$value;
            }
        }

        return class_basename($subject::class).' #'.($subject->getKey() ?? 'n/a');
    }

    public function getChangedFieldsAttribute(): array
    {
        $old = is_array($this->old_values) ? array_keys($this->old_values) : [];
        $new = is_array($this->new_values) ? array_keys($this->new_values) : [];

        return array_values(array_unique(array_merge($old, $new)));
    }

    public function getSummaryAttribute(): string
    {
        $fields = $this->changed_fields;

        if ($fields === []) {
            return Str::headline((string) $this->event);
        }

        return Str::headline((string) $this->event).' · '.implode(', ', $fields);
    }
}
