@php
    $activities = collect();

    foreach (($order?->orderActivities ?? collect()) as $activity) {
        $activities->push((object) [
            'time' => $activity->occurred_at ?? $activity->created_at,
            'type' => $activity->type,
            'title' => $activity->title ?? 'Activity',
            'description' => $activity->description,
            'user' => $activity->user?->name ?? 'System',
            'url' => data_get($activity->metadata, 'file_path') ? \Illuminate\Support\Facades\Storage::disk('public')->url(data_get($activity->metadata, 'file_path')) : null,
        ]);
    }

    foreach (($order?->statusHistories ?? collect()) as $history) {
        $activities->push((object) [
            'time' => $history->changed_at ?? $history->created_at,
            'type' => 'status_changed',
            'title' => 'Status changed',
            'description' => trim(($history->old_status ?? '-') . ' → ' . ($history->new_status ?? '-')),
            'user' => $history->user?->name ?? 'System',
            'url' => null,
        ]);
    }

    foreach (($order?->orderNotes ?? collect()) as $note) {
        $activities->push((object) [
            'time' => $note->created_at,
            'type' => 'note_added',
            'title' => $note->is_pinned ? 'Pinned note added' : 'Note added',
            'description' => $note->note,
            'user' => $note->user?->name ?? 'System',
            'url' => null,
        ]);
    }

    foreach (($order?->attachments ?? collect()) as $attachment) {
        $activities->push((object) [
            'time' => $attachment->created_at,
            'type' => 'attachment_uploaded',
            'title' => 'Attachment uploaded',
            'description' => $attachment->title ?: $attachment->original_name,
            'user' => $attachment->user?->name ?? 'System',
            'url' => $attachment->url ?? null,
        ]);
    }

    foreach (($order?->orderTasks ?? collect()) as $task) {
        $activities->push((object) [
            'time' => $task->created_at,
            'type' => 'task_created',
            'title' => 'Task created',
            'description' => ($task->title ?? '') . ' — ' . ucfirst(str_replace('_', ' ', $task->status ?? 'pending')) . ' / ' . ucfirst($task->priority ?? 'normal'),
            'user' => $task->user?->name ?? 'System',
            'url' => null,
        ]);
    }



    foreach (($order?->orderReminders ?? collect()) as $reminder) {
        $activities->push((object) [
            'time' => $reminder->remind_at ?? $reminder->created_at,
            'type' => 'reminder_created',
            'title' => 'Reminder created',
            'description' => ($reminder->title ?? '') . ' — ' . ucfirst($reminder->status ?? 'pending') . ($reminder->remind_at ? ' / ' . $reminder->remind_at->format('Y-m-d H:i') : ''),
            'user' => $reminder->user?->name ?? 'System',
            'url' => null,
        ]);
    }

    $activities = $activities
        ->filter(fn ($item) => $item->time !== null)
        ->sortByDesc(fn ($item) => $item->time?->timestamp ?? 0)
        ->values();

    $typeStyles = [
        'status_changed' => ['icon' => '↔', 'bg' => '#dbeafe', 'color' => '#1e40af'],
        'note_added' => ['icon' => '✎', 'bg' => '#fef3c7', 'color' => '#92400e'],
        'attachment_uploaded' => ['icon' => '📎', 'bg' => '#dcfce7', 'color' => '#166534'],
        'task_created' => ['icon' => '✓', 'bg' => '#ede9fe', 'color' => '#5b21b6'],
        'reminder_created' => ['icon' => '⏰', 'bg' => '#fee2e2', 'color' => '#991b1b'],
    ];
@endphp

<div style="display:flex;flex-direction:column;gap:12px;">
    @forelse ($activities as $item)
        @php($style = $typeStyles[$item->type] ?? ['icon' => '•', 'bg' => '#f3f4f6', 'color' => '#374151'])

        <div style="display:flex;gap:12px;border:1px solid #e5e7eb;border-radius:14px;padding:14px;background:#fff;">
            <div style="width:34px;height:34px;border-radius:999px;display:flex;align-items:center;justify-content:center;font-weight:800;background:{{ $style['bg'] }};color:{{ $style['color'] }};flex-shrink:0;">
                {{ $style['icon'] }}
            </div>

            <div style="flex:1;min-width:0;">
                <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                    <div style="font-weight:700;color:#111827;">
                        {{ $item->title }}
                    </div>

                    <div style="font-size:12px;color:#6b7280;">
                        {{ $item->time?->format('Y-m-d H:i') }}
                    </div>
                </div>

                @if (! blank($item->description))
                    <div style="margin-top:5px;color:#4b5563;font-size:13px;line-height:1.6;white-space:pre-line;">
                        {{ $item->description }}
                    </div>
                @endif

                <div style="margin-top:8px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;color:#6b7280;font-size:12px;">
                    <span>By: {{ $item->user }}</span>

                    @if ($item->url)
                        <a href="{{ $item->url }}" target="_blank" style="font-weight:700;color:#2563eb;text-decoration:none;">
                            Open file
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div style="border:1px dashed #d1d5db;border-radius:14px;padding:20px;text-align:center;color:#6b7280;background:#fafafa;">
            No activity recorded yet.
        </div>
    @endforelse
</div>
