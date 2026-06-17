@php
    $attachments = $attachments ?? $order?->attachments ?? collect();
@endphp

<div class="space-y-4">
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-700">
        These attachments are internal admin files only. They are not visible to customers.
    </div>

    @if($attachments->isEmpty())
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
            No attachments uploaded for this order yet.
        </div>
    @else
        <div class="space-y-3">
            @foreach($attachments as $attachment)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $attachment->title ?: $attachment->original_name ?: 'Attachment #' . $attachment->id }}
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                Uploaded by:
                                <span class="font-medium text-gray-700">
                                    {{ $attachment->user?->name ?? 'System' }}
                                </span>
                                <span class="mx-2">•</span>
                                {{ optional($attachment->created_at)->format('Y-m-d H:i') }}
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                File:
                                <span class="font-medium text-gray-700">
                                    {{ $attachment->original_name ?: basename((string) $attachment->file_path) }}
                                </span>
                                <span class="mx-2">•</span>
                                {{ $attachment->mime_type ?: '-' }}
                                <span class="mx-2">•</span>
                                {{ $attachment->formatted_size }}
                            </div>
                        </div>

                        @if($attachment->url)
                            <a
                                href="{{ $attachment->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-3 py-2 text-xs font-semibold text-white hover:bg-primary-500"
                            >
                                Open File
                            </a>
                        @endif
                    </div>

                    @if(! empty($attachment->notes))
                        <div class="mt-3 rounded-lg bg-gray-50 p-3 text-sm text-gray-700">
                            {{ $attachment->notes }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
