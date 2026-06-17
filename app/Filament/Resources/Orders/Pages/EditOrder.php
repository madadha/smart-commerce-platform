<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Mail\StorefrontOrderCompletedMail;
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
                    $this->record->orderNotes()->create([
                        'user_id' => auth()->id(),
                        'type' => 'internal',
                        'note' => $data['note'],
                        'is_pinned' => (bool) ($data['is_pinned'] ?? false),
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
                        ->required()
                        ->maxSize(10240),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->maxLength(2000),

                    Toggle::make('is_private')
                        ->label('Private admin file')
                        ->default(true)
                        ->helperText('Attachments are for admin use only and are not shown to customers.'),
                ])
                ->modalContent(fn () => view('filament.orders.attachments-modal', [
                    'order' => $this->record->fresh(['attachments.user']),
                ]))
                ->action(function (array $data): void {
                    $filePath = $data['file'] ?? null;

                    if (is_array($filePath)) {
                        $filePath = reset($filePath) ?: null;
                    }

                    if (blank($filePath)) {
                        Notification::make()
                            ->title('No file selected')
                            ->danger()
                            ->send();

                        return;
                    }

                    $disk = 'public';
                    $originalName = basename((string) $filePath);
                    $mimeType = null;
                    $sizeBytes = null;

                    try {
                        if (Storage::disk($disk)->exists($filePath)) {
                            $mimeType = Storage::disk($disk)->mimeType($filePath);
                            $sizeBytes = Storage::disk($disk)->size($filePath);
                        }
                    } catch (Throwable $exception) {
                        report($exception);
                    }

                    $this->record->attachments()->create([
                        'user_id' => auth()->id(),
                        'title' => $data['title'] ?: $originalName,
                        'original_name' => $originalName,
                        'file_path' => $filePath,
                        'disk' => $disk,
                        'mime_type' => $mimeType,
                        'size_bytes' => $sizeBytes,
                        'notes' => $data['notes'] ?? null,
                        'is_private' => (bool) ($data['is_private'] ?? true),
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
            $this->record->statusHistories()->create([
                'user_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => 'Order status changed from admin panel.',
                'changed_at' => now(),
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
}
