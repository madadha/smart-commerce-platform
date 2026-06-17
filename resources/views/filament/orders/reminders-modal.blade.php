@php
    $reminders = $reminders ?? $order?->orderReminders ?? collect();

    $statusClasses = [
        'pending' => 'background:#fef3c7;color:#92400e;',
        'done' => 'background:#dcfce7;color:#166534;',
        'cancelled' => 'background:#fee2e2;color:#991b1b;',
    ];
@endphp

<div style="display:flex;flex-direction:column;gap:14px;">
    @forelse ($reminders as $reminder)
        <div style="border:1px solid #e5e7eb;border-radius:14px;padding:14px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04);">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                <div style="display:flex;flex-direction:column;gap:6px;min-width:220px;">
                    <div style="font-weight:700;color:#111827;font-size:15px;">
                        {{ $reminder->title }}
                    </div>

                    @if (! blank($reminder->notes))
                        <div style="color:#4b5563;font-size:13px;line-height:1.6;white-space:pre-line;">
                            {{ $reminder->notes }}
                        </div>
                    @endif
                </div>

                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <span style="padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;{{ $statusClasses[$reminder->status] ?? $statusClasses['pending'] }}">
                        {{ $reminder->status_label ?? ucfirst($reminder->status) }}
                    </span>

                    @if ($reminder->is_overdue)
                        <span style="padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;background:#fee2e2;color:#991b1b;">
                            Overdue
                        </span>
                    @endif
                </div>
            </div>

            <div style="margin-top:12px;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:8px;color:#6b7280;font-size:12px;">
                <div>
                    <strong style="color:#374151;">Created by:</strong>
                    {{ $reminder->user?->name ?? 'System' }}
                </div>

                <div>
                    <strong style="color:#374151;">Assigned to:</strong>
                    {{ $reminder->assignedTo?->name ?? 'Not assigned' }}
                </div>

                <div>
                    <strong style="color:#374151;">Reminder:</strong>
                    {{ $reminder->remind_at?->format('Y-m-d H:i') ?? '-' }}
                </div>

                <div>
                    <strong style="color:#374151;">Created:</strong>
                    {{ $reminder->created_at?->format('Y-m-d H:i') ?? '-' }}
                </div>
            </div>
        </div>
    @empty
        <div style="border:1px dashed #d1d5db;border-radius:14px;padding:20px;text-align:center;color:#6b7280;background:#fafafa;">
            No internal reminders yet.
        </div>
    @endforelse
</div>
