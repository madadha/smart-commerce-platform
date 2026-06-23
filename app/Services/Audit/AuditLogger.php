<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class AuditLogger
{
    /**
     * @var array<int, array{old_values: array<string, mixed>, new_values: array<string, mixed>}>
     */
    protected array $pendingUpdates = [];

    public function captureUpdate(Model $model): void
    {
        $dirty = $this->sanitizeAttributes($model->getDirty(), $model);

        if ($dirty === []) {
            return;
        }

        $this->pendingUpdates[spl_object_id($model)] = [
            'old_values' => $this->sanitizeAttributes(Arr::only($model->getOriginal(), array_keys($dirty)), $model, true),
            'new_values' => $dirty,
        ];
    }

    public function recordCreated(Model $model): void
    {
        $this->record('created', $model, [], $this->sanitizeAttributes($model->getAttributes(), $model));
    }

    public function recordUpdated(Model $model): void
    {
        $pending = $this->pendingUpdates[spl_object_id($model)] ?? null;

        if ($pending === null) {
            $changes = $this->sanitizeAttributes($model->getChanges(), $model);

            if ($changes === []) {
                return;
            }

            $this->record(
                'updated',
                $model,
                $this->sanitizeAttributes(Arr::only($model->getOriginal(), array_keys($changes)), $model, true),
                $changes
            );

            return;
        }

        unset($this->pendingUpdates[spl_object_id($model)]);

        if ($pending['old_values'] === [] && $pending['new_values'] === []) {
            return;
        }

        $this->record('updated', $model, $pending['old_values'], $pending['new_values']);
    }

    public function recordDeleted(Model $model): void
    {
        $this->record('deleted', $model, $this->sanitizeAttributes($model->getAttributes(), $model), []);
    }

    protected function record(string $event, Model $model, array $oldValues, array $newValues): void
    {
        $userId = auth()->id();

        if (! $userId) {
            return;
        }

        try {
            AuditLog::query()->create([
                'user_id' => $userId,
                'event' => $event,
                'subject_type' => $model::class,
                'subject_id' => $model->getKey(),
                'route' => request()?->route()?->getName(),
                'method' => request()?->method(),
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'metadata' => [
                    'model' => $model::class,
                    'label' => class_basename($model::class),
                ],
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    protected function sanitizeAttributes(array $attributes, Model $model, bool $isOriginal = false): array
    {
        $sanitized = [];

        foreach ($attributes as $key => $value) {
            $sanitized[$key] = $this->sanitizeValue((string) $key, $value, $model, $isOriginal);
        }

        return $sanitized;
    }

    protected function sanitizeValue(string $key, mixed $value, Model $model, bool $isOriginal = false): mixed
    {
        if ($this->isSensitiveKey($key, $model)) {
            return '[redacted]';
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_array($value)) {
            $result = [];

            foreach ($value as $nestedKey => $nestedValue) {
                $result[$nestedKey] = $this->sanitizeValue((string) $nestedKey, $nestedValue, $model, $isOriginal);
            }

            return $result;
        }

        if (is_object($value)) {
            if ($value instanceof Arrayable) {
                return $value->toArray();
            }

            if ($value instanceof \JsonSerializable) {
                return $value->jsonSerialize();
            }

            return method_exists($value, '__toString') ? (string) $value : get_class($value);
        }

        if (is_bool($value) || is_int($value) || is_float($value) || is_string($value) || $value === null) {
            return $value;
        }

        return (array) $value;
    }

    protected function isSensitiveKey(string $key, Model $model): bool
    {
        $normalized = Str::snake($key);

        $exact = [
            'password',
            'remember_token',
            'api_key',
            'secret_key',
            'client_secret',
            'client_token',
            'webhook_secret',
            'webhook_id',
            'payment_page_uid',
            'terminal_uid',
            'sandbox_credentials',
            'live_credentials',
            'provider_payload',
            'digital_code',
        ];

        if (in_array($normalized, $exact, true)) {
            return true;
        }

        return Str::contains($normalized, ['password', 'secret', 'token', 'credential']);
    }
}
