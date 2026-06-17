@php
    $order = $order ?? null;

    $activities = collect();

    if ($order) {
        foreach (($order->orderActivities ?? collect()) as $activity) {
            $activities->push([
                'type' => $activity->type ?? 'activity',
                'title' => $activity->title ?? 'Activity',
                'description' => $activity->description,
                'user' => $activity->user?->name ?? 'System',
                'date' => $activity->occurred_at ?? $activity->created_at,
                'color' => match ($activity->type) {
                    'status_changed' => 'blue',
                    'note_added' => 'amber',
                    'attachment_uploaded' => 'emerald',
                    default => 'gray',
                },
            ]);
        }

        foreach (($order->statusHistories ?? collect()) as $history) {
            $activities->push([
                'type' => 'status_changed',
                'title' => 'Status changed',
                'description' => ($history->old_status ?: '-') . ' → ' . ($history->new_status ?: '-'),
                'user' => $history->user?->name ?? 'System',
                'date' => $history->changed_at ?? $history->created_at,
                'color' => 'blue',
            ]);
        }

        foreach (($order->orderNotes ?? collect()) as $note) {
            $activities->push([
                'type' => 'note_added',
                'title' => ($note->is_pinned ? 'Pinned note' : 'Internal note'),
                'description' => $note->note,
                'user' => $note->user?->name ?? 'System',
                'date' => $note->created_at,
                'color' => 'amber',
            ]);
        }

        foreach (($order->orderAttachments ?? collect()) as $attachment) {
            $activities->push([
                'type' => 'attachment_uploaded',
                'title' => 'Attachment uploaded',
                'description' => $attachment->title ?: ($attachment->original_name ?? basename((string) $attachment->file_path)),
                'user' => $attachment->user?->name ?? 'System',
                'date' => $attachment->created_at,
                'color' => 'emerald',
                'url' => $attachment->url ?? null,
            ]);
        }
    }

    $activities = $activities
        ->sortByDesc(fn ($item) => optional($item['date'])->timestamp ?? 0)
        ->values();
@endphp

<div class="space-y-4">
    @if($activities->isEmpty())
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
            No activity recorded yet for this order.
        </div>
    @else
        <div class="space-y-3">
            @foreach($activities as $activity)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex gap-3">
                            <div class="mt-1 h-3 w-3 rounded-full
                                @if($activity['color'] === 'blue') bg-blue-500
                                @elseif($activity['color'] === 'amber') bg-amber-500
                                @elseif($activity['color'] === 'emerald') bg-emerald-500
                                @else bg-gray-400
                                @endif
                            "></div>

                            <div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $activity['title'] }}
                                </div>

                                @if(! empty($activity['description']))
                                    <div class="mt-1 text-sm text-gray-700">
                                        {{ $activity['description'] }}
                                    </div>
                                @endif

                                @if(! empty($activity['url']))
                                    <a href="{{ $activity['url'] }}" target="_blank" class="mt-2 inline-block text-xs font-medium text-primary-600 hover:underline">
                                        Open attachment
                                    </a>
                                @endif

                                <div class="mt-2 text-xs text-gray-500">
                                    By {{ $activity['user'] }}
                                </div>
                            </div>
                        </div>

                        <div class="text-xs text-gray-500">
                            {{ optional($activity['date'])->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
