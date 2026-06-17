@php
    $tasks = $tasks ?? $order?->orderTasks ?? collect();

    $statusClasses = [
        'pending' => 'background:#fef3c7;color:#92400e;',
        'in_progress' => 'background:#dbeafe;color:#1e40af;',
        'done' => 'background:#dcfce7;color:#166534;',
        'cancelled' => 'background:#fee2e2;color:#991b1b;',
    ];

    $priorityClasses = [
        'low' => 'background:#f3f4f6;color:#374151;',
        'normal' => 'background:#e0f2fe;color:#075985;',
        'high' => 'background:#ffedd5;color:#9a3412;',
        'urgent' => 'background:#fee2e2;color:#991b1b;',
    ];
@endphp

<div style="display:flex;flex-direction:column;gap:14px;">
    @forelse ($tasks as $task)
        <div style="border:1px solid #e5e7eb;border-radius:14px;padding:14px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04);">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                <div style="display:flex;flex-direction:column;gap:6px;min-width:220px;">
                    <div style="font-weight:700;color:#111827;font-size:15px;">
                        {{ $task->title }}
                    </div>

                    @if (! blank($task->description))
                        <div style="color:#4b5563;font-size:13px;line-height:1.6;white-space:pre-line;">
                            {{ $task->description }}
                        // </div>
                    @endif
                </div>

                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <span style="padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;{{ $statusClasses[$task->status] ?? $statusClasses['pending'] }}">
                        {{ $task->status_label ?? ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>

                    <span style="padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;{{ $priorityClasses[$task->priority] ?? $priorityClasses['normal'] }}">
                        {{ $task->priority_label ?? ucfirst($task->priority) }}
                    </span>
                </div>
            </div>

            <div style="margin-top:12px;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:8px;color:#6b7280;font-size:12px;">
                <div>
                    <strong style="color:#374151;">Created by:</strong>
                    {{ $task->user?->name ?? 'System' }}
                </div>

                <div>
                    <strong style="color:#374151;">Assigned to:</strong>
                    {{ $task->assignedTo?->name ?? 'Not assigned' }}
                </div>

                <div>
                    <strong style="color:#374151;">Due:</strong>
                    {{ $task->due_at?->format('Y-m-d H:i') ?? '-' }}
                </div>

                <div>
                    <strong style="color:#374151;">Created:</strong>
                    {{ $task->created_at?->format('Y-m-d H:i') ?? '-' }}
                </div>
            </div>
        </div>
    @empty
        <div style="border:1px dashed #d1d5db;border-radius:14px;padding:20px;text-align:center;color:#6b7280;background:#fafafa;">
            No internal tasks yet.
        </div>
    @endforelse
</div>
