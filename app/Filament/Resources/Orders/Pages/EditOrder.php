<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Mail\StorefrontOrderCompletedMail;
use App\Models\OrderAttachment;
use App\Models\OrderNote;
use App\Models\OrderStatusHistory;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected ?string $oldOrderStatus = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('order_activity')
                ->label('Order Activity')
                ->icon('heroicon-o-list-bullet')
                ->color('success')
                ->modalHeading(fn () => 'Order Activity - ' . ($this->record->order_number ?? ('#' . $this->record->id)))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalWidth('4xl')
                ->modalContent(fn () => view('filament.orders.activity-modal', [
                    'order' => $this->record->fresh([
                        'orderActivities.user',
                        'statusHistories.user',
                        'orderNotes.user',
                        'orderAttachments.user',
                    ]),
                ])),

            Action::make('status_history')
                ->label('Status History')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->modalHeading(fn () => 'Status History - ' . ($this->record->order_number ?? ('#' . $this->record->id)))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalWidth('3xl')
                ->modalContent(fn () => view('filament.orders.status-history-modal', [
                    'order' => $this->record->fresh(['statusHistories.user']),
                ])),

            Action::make('order_notes')
                ->label('Order Notes')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('warning')
                ->modalHeading(fn () => 'Order Notes - ' . ($this->record->order_number ?? ('#' . $this->record->id)))
                ->modalSubmitActionLabel('Add Note')
                ->modalCancelActionLabel('Close')
                ->modalWidth('3xl')
                ->schema([
                    Textarea::make('note')
                        ->label('New Internal Note')
                        ->rows(4)
                        ->required()
                        ->maxLength(2000)
                        ->helperText('This note is internal and will not be visible to the customer.'),

                    Toggle::make('is_pinned')
                        ->label('Pin this note')
                        ->default(false),
                ])
                ->modalContent(fn () => view('filament.orders.notes-modal', [
                    'order' => $this->record->fresh(['orderNotes.user']),
                ]))
                ->action(function (array $data): void {
                    $note = $this->record->orderNotes()->create([
                        'user_id' => auth()->id(),
                        'type' => 'internal',
                        'note' => $data['note'],
                        'is_pinned' => (bool) ($data['is_pinned'] ?? false),
                    ]);

                    $this->record->orderActivities()->create([
                        'user_id' => auth()->id(),
                        'type' => 'note_added',
                        'title' => 'Internal note added',
                        'description' => (string) ($data['note'] ?? ''),
                        'subject_type' => OrderNote::class,
                        'subject_id' => $note->id,
                        'metadata' => [
                            'is_pinned' => (bool) ($data['is_pinned'] ?? false),
                        ],
                        'occurred_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Order note added')
                        ->success()
                        ->send();
                }),

            Action::make('order_attachments')
                ->label('Order Attachments')
                ->icon('heroicon-o-paper-clip')
                ->color('info')
                ->modalHeading(fn () => 'Order Attachments - ' . ($this->record->order_number ?? ('#' . $this->record->id)))
                ->modalSubmitActionLabel('Upload Attachment')
                ->modalCancelActionLabel('Close')
                ->modalWidth('4xl')
                ->schema([
                    TextInput::make('title')
                        ->label('Attachment Title')
                        ->maxLength(255)
                        ->placeholder('Payment proof, shipping label, WhatsApp screenshot...'),

                    FileUpload::make('file')
                        ->label('File')
                        ->disk('public')
                        ->directory('order-attachments')
                        ->preserveFilenames()
                        ->downloadable()
                        ->openable()
                        ->required(),

                    Textarea::make('notes')
                        ->label('Attachment Notes')
                        ->rows(3)
                        ->maxLength(1000),

                    Toggle::make('is_private')
                        ->label('Private admin file')
                        ->default(true),
                ])
                ->modalContent(fn () => view('filament.orders.attachments-modal', [
                    'order' => $this->record->fresh(['orderAttachments.user']),
                ]))
                ->action(function (array $data): void {
                    $filePath = $this->normalizeUploadedFilePath($data['file'] ?? null);

                    if (! $filePath) {
                        Notification::make()
                            ->title('No file selected')
                            ->danger()
                            ->send();

                        return;
                    }

                    $disk = 'public';
                    $originalName = basename($filePath);
                    $mimeType = null;
                    $sizeBytes = null;

                    try {
                        $mimeType = Storage::disk($disk)->mimeType($filePath);
                    } catch (Throwable) {
                        $mimeType = null;
                    }

                    try {
                        $sizeBytes = Storage::disk($disk)->size($filePath);
                    } catch (Throwable) {
                        $sizeBytes = null;
                    }

                    $attachment = $this->record->orderAttachments()->create([
                        'user_id' => auth()->id(),
                        'title' => $data['title'] ?? null,
                        'original_name' => $originalName,
                        'file_path' => $filePath,
                        'disk' => $disk,
                        'mime_type' => $mimeType,
                        'size_bytes' => $sizeBytes,
                        'notes' => $data['notes'] ?? null,
                        'is_private' => (bool) ($data['is_private'] ?? true),
                    ]);

                    $this->record->orderActivities()->create([
                        'user_id' => auth()->id(),
                        'type' => 'attachment_uploaded',
                        'title' => 'Attachment uploaded',
                        'description' => $data['title'] ?: $originalName,
                        'subject_type' => OrderAttachment::class,
                        'subject_id' => $attachment->id,
                        'metadata' => [
                            'original_name' => $originalName,
                            'file_path' => $filePath,
                            'mime_type' => $mimeType,
                            'size_bytes' => $sizeBytes,
                            'is_private' => (bool) ($data['is_private'] ?? true),
                        ],
                        'occurred_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Order attachment uploaded')
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $this->oldOrderStatus = $this->normalizeStatusValue($this->record->getOriginal('status'));
    }

    protected function afterSave(): void
    {
        $oldStatus = $this->oldOrderStatus;
        $newStatus = $this->normalizeStatusValue($this->record->status);

        if ($oldStatus !== null && $newStatus !== null && $oldStatus !== $newStatus) {
            $history = $this->record->statusHistories()->create([
                'user_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => 'Order status changed from admin panel.',
                'changed_at' => now(),
            ]);

            $this->record->orderActivities()->create([
                'user_id' => auth()->id(),
                'type' => 'status_changed',
                'title' => 'Order status changed',
                'description' => $oldStatus . ' → ' . $newStatus,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'subject_type' => OrderStatusHistory::class,
                'subject_id' => $history->id,
                'metadata' => [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
                'occurred_at' => now(),
            ]);

            Notification::make()
                ->title('Order status history saved')
                ->success()
                ->send();

            if ($newStatus === 'completed') {
                $this->sendCompletedEmailSafely();
            }
        }
    }

    private function sendCompletedEmailSafely(): void
    {
        try {
            $order = $this->record->fresh([
                'items.product',
                'items.product.currency',
                'currency',
                'customer',
                'shippingMethod',
            ]);

            $customerEmail = $order->customer_email
                ?? $order->customer?->email
                ?? null;

            if (! empty($customerEmail)) {
                Mail::to($customerEmail)->send(
                    new StorefrontOrderCompletedMail($order, app()->getLocale() ?: 'ar')
                );
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function normalizeStatusValue(mixed $status): ?string
    {
        if ($status instanceof \BackedEnum) {
            return (string) $status->value;
        }

        if ($status === null || $status === '') {
            return null;
        }

        return (string) $status;
    }

    private function normalizeUploadedFilePath(mixed $file): ?string
    {
        if (is_array($file)) {
            $first = reset($file);

            if (is_array($first)) {
                return $first['path'] ?? $first['file'] ?? null;
            }

            return $first ?: null;
        }

        return $file ? (string) $file : null;
    }
}
