@php
    $order = $order ?? null;

    $activities = $order?->orderActivities ?? collect();
    $statusHistories = $order?->statusHistories ?? collect();
    $notes = $order?->orderNotes ?? collect();
    $attachments = $order?->attachments ?? ($order?->orderAttachments ?? collect());
    $tasks = $order?->orderTasks ?? collect();
    $reminders = $order?->orderReminders ?? collect();

    $lastActivity = $activities->first();
    $lastStatus = $statusHistories->first();
    $lastNote = $notes->first();
    $lastAttachment = $attachments->first();
    $lastTask = $tasks->first();
    $lastReminder = $reminders->first();

    $openTasks = $tasks->whereIn('status', ['pending', 'in_progress'])->count();
    $doneTasks = $tasks->where('status', 'done')->count();
    $urgentTasks = $tasks->where('priority', 'urgent')->whereIn('status', ['pending', 'in_progress'])->count();
    $overdueTasks = $tasks->filter(fn ($task) => in_array($task->status, ['pending', 'in_progress'], true) && $task->due_at && $task->due_at->isPast())->count();

    $pendingReminders = $reminders->where('status', 'pending')->count();
    $overdueReminders = $reminders->filter(fn ($reminder) => $reminder->status === 'pending' && $reminder->remind_at && $reminder->remind_at->isPast())->count();

    $statusValue = $order?->status instanceof \BackedEnum ? $order->status->value : (string) ($order?->status ?? '-');
    $paymentStatusValue = $order?->payment_status instanceof \BackedEnum ? $order->payment_status->value : (string) ($order?->payment_status ?? '-');
    $priorityValue = (string) ($order?->priority ?? 'normal');
    $priorityReason = (string) ($order?->priority_reason ?? 'No priority reason');

    $cards = [
        ['label' => 'Order Status', 'value' => $statusValue, 'hint' => 'Current order status'],
        ['label' => 'Payment Status', 'value' => $paymentStatusValue, 'hint' => 'Current payment state'],
        ['label' => 'Order Priority', 'value' => ucfirst($priorityValue), 'hint' => $priorityReason],
        ['label' => 'Open Tasks', 'value' => $openTasks, 'hint' => $doneTasks . ' completed'],
        ['label' => 'Urgent Tasks', 'value' => $urgentTasks, 'hint' => $overdueTasks . ' overdue tasks'],
        ['label' => 'Pending Reminders', 'value' => $pendingReminders, 'hint' => $overdueReminders . ' overdue reminders'],
        ['label' => 'Attachments', 'value' => $attachments->count(), 'hint' => 'Admin internal files'],
    ];

    $attachmentUrl = null;
    if ($lastAttachment && ! empty($lastAttachment->file_path)) {
        try {
            $attachmentUrl = \Illuminate\Support\Facades\Storage::disk($lastAttachment->disk ?: 'public')->url($lastAttachment->file_path);
        } catch (\Throwable $exception) {
            $attachmentUrl = null;
        }
    }
@endphp

<div class="space-y-5">
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                    Follow-up Board
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Quick internal overview for this order.
                </p>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ $order?->order_number ?? ('#' . ($order?->id ?? '-')) }}
            </div>
        </div>
    </div>

    @if (in_array($priorityValue, ['high', 'urgent'], true))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
            <strong>Priority:</strong> {{ ucfirst($priorityValue) }}
            @if (! empty($order?->priority_reason))
                <div class="mt-1">{{ $order->priority_reason }}</div>
            @endif
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($cards as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ $card['label'] }}
                </div>
                <div class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                    {{ $card['value'] }}
                </div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $card['hint'] }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h4 class="font-semibold text-gray-950 dark:text-white">Latest Status Change</h4>
            @if ($lastStatus)
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                    <span class="font-medium">{{ $lastStatus->old_status ?? '-' }}</span>
                    <span class="mx-1">→</span>
                    <span class="font-medium">{{ $lastStatus->new_status ?? '-' }}</span>
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $lastStatus->user?->name ?? 'System' }} · {{ optional($lastStatus->changed_at ?? $lastStatus->created_at)->format('Y-m-d H:i') }}
                </p>
            @else
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No status history yet.</p>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h4 class="font-semibold text-gray-950 dark:text-white">Latest Note</h4>
            @if ($lastNote)
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">{{ \Illuminate\Support\Str::limit($lastNote->note, 160) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $lastNote->user?->name ?? 'System' }} · {{ optional($lastNote->created_at)->format('Y-m-d H:i') }}
                </p>
            @else
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No internal notes yet.</p>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h4 class="font-semibold text-gray-950 dark:text-white">Latest Attachment</h4>
            @if ($lastAttachment)
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                    {{ $lastAttachment->display_name ?? $lastAttachment->title ?? $lastAttachment->original_name ?? basename((string) $lastAttachment->file_path) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $lastAttachment->user?->name ?? 'System' }} · {{ optional($lastAttachment->created_at)->format('Y-m-d H:i') }}
                </p>
                @if ($attachmentUrl)
                    <a href="{{ $attachmentUrl }}" target="_blank" class="mt-2 inline-flex text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">
                        Open attachment
                    </a>
                @endif
            @else
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No attachments yet.</p>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h4 class="font-semibold text-gray-950 dark:text-white">Latest Activity</h4>
            @if ($lastActivity)
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                    <span class="font-medium">{{ $lastActivity->title ?? $lastActivity->type }}</span>
                    @if ($lastActivity->description)
                        <span class="block mt-1">{{ \Illuminate\Support\Str::limit($lastActivity->description, 160) }}</span>
                    @endif
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $lastActivity->user?->name ?? 'System' }} · {{ optional($lastActivity->occurred_at ?? $lastActivity->created_at)->format('Y-m-d H:i') }}
                </p>
            @else
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No activity yet.</p>
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <h4 class="font-semibold text-gray-950 dark:text-white">Next Follow-up Items</h4>

        <div class="mt-3 space-y-3">
            @forelse ($tasks->whereIn('status', ['pending', 'in_progress'])->take(5) as $task)
                <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div class="font-medium text-gray-950 dark:text-white">{{ $task->title }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ strtoupper($task->priority ?? 'normal') }} · {{ $task->status_label ?? $task->status }}
                        </div>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Assigned: {{ $task->assignedTo?->name ?? '-' }}
                        @if ($task->due_at)
                            · Due: {{ $task->due_at->format('Y-m-d H:i') }}
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No open tasks.</p>
            @endforelse

            @foreach ($reminders->where('status', 'pending')->take(5) as $reminder)
                <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div class="font-medium text-gray-950 dark:text-white">{{ $reminder->title }}</div>
                        <div class="text-xs {{ $reminder->is_overdue ? 'text-danger-600 dark:text-danger-400' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $reminder->is_overdue ? 'OVERDUE' : 'REMINDER' }}
                        </div>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Assigned: {{ $reminder->assignedTo?->name ?? '-' }}
                        @if ($reminder->remind_at)
                            · Remind at: {{ $reminder->remind_at->format('Y-m-d H:i') }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
