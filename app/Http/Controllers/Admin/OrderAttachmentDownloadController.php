<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderAttachmentDownloadController extends Controller
{
    public function __invoke(Request $request, OrderAttachment $attachment): StreamedResponse
    {
        $order = $attachment->order;

        abort_unless($order && $request->user()?->can('view', $order), 403);

        $disk = in_array($attachment->disk, ['local', 'public'], true)
            ? $attachment->disk
            : abort(404);

        abort_unless($attachment->file_path && Storage::disk($disk)->exists($attachment->file_path), 404);

        return Storage::disk($disk)->download(
            $attachment->file_path,
            $attachment->original_name ?: basename($attachment->file_path),
        );
    }
}
