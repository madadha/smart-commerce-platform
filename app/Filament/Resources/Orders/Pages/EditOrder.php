<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Mail\StorefrontOrderCompletedMail;
use App\Models\OrderAttachment;
use App\Models\OrderNote;
use App\Models\OrderStatusHistory;
use App\Models\OrderTask;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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
                        'attachments.user',
                        'orderTasks.user',
                        'orderTasks.assignedTo',
                    ]),
                ])),

            Action::make('order_tasks')
                ->label('Order Tasks')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->modalHeading(fn () => 'Order Tasks - ' . ($this->record->order_number ?? ('#' . $this->record->id)))
                ->modalSubmitActionLabel('Add Task')
                ->modalCancelActionLabel('Close')
                ->modalWidth('4xl')
                ->schema([
                    TextInput::make('title')
                        ->label('Task Title')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Call customer, verify payment, prepare order...'),

                    Textarea::make('description')
                        ->label('Description / Notes')
                        ->rows(3)
                        ->maxLength(2000),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'in_progress' => 'In Progress',
                            'done' => 'Done',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('pending')
                        ->required(),

                    Select::make('priority')
                        ->label('Priority')
                        ->options([
                            'low' => 'Low',
                            'normal' => 'Normal',
                            'high' => 'High',
                            'urgent' => 'Urgent',
                        ])
                        ->default('normal')
                        ->required(),

                    Select::make('assigned_to_user_id')
                        ->label('Assigned To')
                        ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->id()),

                    DateTimePicker::make('due_at')
                        ->label('Due Date')
                        ->seconds(false),

                    Toggle::make('is_private')
                        ->label('Private admin task')
                        ->default(true),
                ])
                ->modalContent(fn () => view('filament.orders.tasks-modal', [
                    'order' => $this->record->fresh(['orderTasks.user', 'orderTasks.assignedTo']),
                ]))
                ->action(function (array $data): void {
                    $status = (string) ($data['status'] ?? 'pending');

                    $task = $this->record->orderTasks()->create([
                        'user_id' => auth()->id(),
                        'assigned_to_user_id' => $data['assigned_to_user_id'] ?? null,
                        'title' => $data['title'],
                        'description' => $data['description'] ?? null,
                        'status' => $status,
                        'priority' => $data['priority'] ?? 'normal',
                        'due_at' => $data['due_at'] ?? null,
                        'completed_at' => $status === 'done' ? now() : null,
                        'is_private' => (bool) ($data['is_private'] ?? true),
                    ]);

                    $this->record->orderActivities()->create([
                        'user_id' => auth()->id(),
                        'type' => 'task_created',
                        'title' => 'Task created',
                        'description' => $task->title,
                        'subject_type' => OrderTask::class,
                        'subject_id' => $task->id,
                        'metadata' => [
                            'status' => $task->status,
                            'priority' => $task->priority,
                            'assigned_to_user_id' => $task->assigned_to_user_id,
                            'due_at' => $task->due_at?->toDateTimeString(),
                        ],
                        'occurred_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Order task added')
                        ->success()
                        ->send();
                }),

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
                        'title' => 'Note added',
                        'description' => $note->note,
                        'subject_type' => OrderNote::class,
                        'subject_id' => $note->id,
                        'metadata' => [
                            'is_pinned' => (bool) $note->is_pinned,
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
                        ->disk('local')
                        ->directory('order-attachments')
                        ->visibility('private')
                        ->preserveFilenames(false)
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'text/plain',
                            'text/csv',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
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
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Attachments are stored privately and are only available to authorized administrators.'),
                ])
                ->modalContent(fn () => view('filament.orders.attachments-modal', [
                    'order' => $this->record->fresh(['attachments.user']),
                ]))
                ->action(function (array $data): void {
                    $filePath = $this->normalizeUploadedFilePath($data['file'] ?? null);

                    if (blank($filePath)) {
                        Notification::make()
                            ->title('No file selected')
                            ->danger()
                            ->send();

                        return;
                    }

                    $disk = 'local';
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

                    $attachment = $this->record->attachments()->create([
                        'user_id' => auth()->id(),
                        'title' => $data['title'] ?: $originalName,
                        'original_name' => $originalName,
                        'file_path' => $filePath,
                        'disk' => $disk,
                        'mime_type' => $mimeType,
                        'size_bytes' => $sizeBytes,
                        'notes' => $data['notes'] ?? null,
                        'is_private' => true,
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
                'title' => 'Status changed',
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
                    new StorefrontOrderCompletedMail($order, $order->locale ?: 'ar')
                );
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function normalizeUploadedFilePath(mixed $file): ?string
    {
        if (is_array($file)) {
            $file = reset($file) ?: null;
        }

        if ($file === null || $file === '') {
            return null;
        }

        return (string) $file;
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
