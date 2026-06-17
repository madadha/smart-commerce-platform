@php
    $order = $order ?? null;

    $notes = collect();

    if ($order) {
        $notes = $order->relationLoaded('orderNotes')
            ? $order->orderNotes
            : $order->orderNotes()->with('user')->latest()->get();
    }
@endphp

<div class="space-y-4">
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        هذه الملاحظات داخلية للوحة الإدارة فقط، ولا تظهر للزبون.
    </div>

    @if($notes->isEmpty())
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
            No internal notes recorded yet for this order.
        </div>
    @else
        <div class="space-y-3">
            @foreach($notes as $note)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm font-semibold text-gray-900">
                            {{ $note->user?->name ?? 'System' }}

                            @if($note->is_pinned)
                                <span class="ms-2 rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">
                                    Pinned
                                </span>
                            @endif
                        </div>

                        <div class="text-xs text-gray-500">
                            {{ optional($note->created_at)->format('Y-m-d H:i') }}
                        </div>
                    </div>

                    <div class="mt-3 whitespace-pre-line rounded-lg bg-gray-50 p-3 text-sm leading-6 text-gray-700">
                        {{ $note->note }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
