@php
    $histories = $histories ?? $order?->statusHistories ?? collect();
@endphp

<div class="space-y-4">
    @if ($histories->isEmpty())
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 text-center dark:border-gray-700 dark:bg-gray-800">
            <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                No status history recorded yet.
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Status changes will appear here after updating the order status.
            </div>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($histories as $history)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $history->old_status ?: '—' }}
                                <span class="mx-2 text-gray-400">→</span>
                                {{ $history->new_status ?: '—' }}
                            </div>

                            @if (! empty($history->note))
                                <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $history->note }}
                                </div>
                            @endif

                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                By:
                                {{ $history->user?->name ?? 'System' }}
                            </div>
                        </div>

                        <div class="shrink-0 text-xs text-gray-500 dark:text-gray-400">
                            {{ optional($history->changed_at ?? $history->created_at)->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>