@php
    /** @var \App\Models\AuditLog $record */
    $oldValues = $record->old_values ?? [];
    $newValues = $record->new_values ?? [];
    $metadata = $record->metadata ?? [];
@endphp

<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-sm font-semibold text-gray-700">Context</div>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Actor</dt><dd class="font-medium text-gray-900">{{ $record->user?->name ?? 'System' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Event</dt><dd class="font-medium text-gray-900">{{ $record->event }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Subject</dt><dd class="font-medium text-gray-900">{{ $record->subject_label }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Route</dt><dd class="font-medium text-gray-900">{{ $record->route ?? 'n/a' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Method</dt><dd class="font-medium text-gray-900">{{ $record->method ?? 'n/a' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">IP</dt><dd class="font-medium text-gray-900">{{ $record->ip_address ?? 'n/a' }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-sm font-semibold text-gray-700">Metadata</div>
            <pre class="mt-3 overflow-auto rounded-xl bg-gray-950 p-4 text-xs leading-5 text-gray-100">{{ json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-sm font-semibold text-gray-700">Old Values</div>
            <pre class="mt-3 overflow-auto rounded-xl bg-gray-950 p-4 text-xs leading-5 text-gray-100">{{ json_encode($oldValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-sm font-semibold text-gray-700">New Values</div>
            <pre class="mt-3 overflow-auto rounded-xl bg-gray-950 p-4 text-xs leading-5 text-gray-100">{{ json_encode($newValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
</div>
