@php
    /**
     * This view supports both cases:
     * 1) EditOrder passes $histories directly.
     * 2) EditOrder passes $order only.
     */
    $histories = $histories ?? null;

    if (! $histories && isset($order) && $order) {
        $histories = $order->statusHistories()
            ->with('user')
            ->latest('changed_at')
            ->latest('id')
            ->get();
    }

    $histories = $histories ?? collect();

    $formatStatus = function (?string $status): string {
        if (blank($status)) {
            return '-';
        }

        return ucfirst(str_replace('_', ' ', $status));
    };

    $badgeStyle = function (?string $status): string {
        return match ($status) {
            'pending' => 'background:#fef3c7;color:#92400e;border-color:#fde68a;',
            'processing' => 'background:#dbeafe;color:#1d4ed8;border-color:#bfdbfe;',
            'shipped' => 'background:#ede9fe;color:#6d28d9;border-color:#ddd6fe;',
            'completed' => 'background:#dcfce7;color:#166534;border-color:#bbf7d0;',
            'cancelled', 'canceled' => 'background:#fee2e2;color:#991b1b;border-color:#fecaca;',
            'refunded' => 'background:#f3f4f6;color:#374151;border-color:#e5e7eb;',
            default => 'background:#f9fafb;color:#374151;border-color:#e5e7eb;',
        };
    };
@endphp

<div style="font-family: Arial, sans-serif;">
    @if($histories->isEmpty())
        <div style="padding:18px;border:1px dashed #d1d5db;border-radius:12px;color:#6b7280;background:#f9fafb;">
            No status history recorded yet.
        </div>
    @else
        <div style="display:grid;gap:12px;">
            @foreach($histories as $history)
                @php
                    $oldStatus = $history->old_status ?: null;
                    $newStatus = $history->new_status ?: null;
                    $changedAt = $history->changed_at ?? $history->created_at;
                @endphp

                <div style="padding:14px;border:1px solid #e5e7eb;border-radius:14px;background:#ffffff;box-shadow:0 1px 2px rgba(15,23,42,.05);">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                        <div>
                            <div style="font-weight:700;color:#111827;margin-bottom:8px;">
                                Status changed
                            </div>

                            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                <span style="display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;font-size:12px;font-weight:600;">
                                    {{ $formatStatus($oldStatus) }}
                                </span>

                                <span style="color:#9ca3af;font-weight:700;">→</span>

                                <span style="display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid;{{ $badgeStyle($newStatus) }}font-size:12px;font-weight:700;">
                                    {{ $formatStatus($newStatus) }}
                                </span>
                            </div>
                        </div>

                        <div style="text-align:right;color:#6b7280;font-size:12px;">
                            <div>{{ optional($changedAt)->format('Y-m-d H:i') ?? '-' }}</div>
                            <div>
                                By:
                                <strong style="color:#111827;">
                                    {{ $history->user?->name ?? 'System' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    @if(! blank($history->note))
                        <div style="margin-top:10px;padding:10px;border-radius:10px;background:#f9fafb;color:#4b5563;font-size:13px;">
                            {{ $history->note }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
