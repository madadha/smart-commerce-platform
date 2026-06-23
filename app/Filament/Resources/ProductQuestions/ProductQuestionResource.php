<?php

namespace App\Filament\Resources\ProductQuestions;

use App\Filament\Resources\ProductQuestions\Pages;
use App\Models\ProductQuestion;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductQuestionResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = ProductQuestion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Storefront';

    protected static ?string $navigationLabel = 'Product Questions';

    protected static ?string $modelLabel = 'Product Question';

    protected static ?string $pluralModelLabel = 'Product Questions';

    protected static ?int $navigationSort = 45;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'sku')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('user_id')
                    ->label('Customer Account')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('customer_name')
                    ->label('Customer Name')
                    ->required()
                    ->maxLength(120),

                TextInput::make('customer_email')
                    ->label('Customer Email')
                    ->email()
                    ->maxLength(180)
                    ->nullable(),

                Textarea::make('question')
                    ->label('Question')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),

                Textarea::make('answer')
                    ->label('Answer')
                    ->rows(5)
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),

                Select::make('locale')
                    ->label('Language')
                    ->options([
                        'ar' => 'Arabic',
                        'he' => 'Hebrew',
                        'en' => 'English',
                    ])
                    ->default('ar')
                    ->required(),

                Select::make('answered_by')
                    ->label('Answered By')
                    ->relationship('answeredBy', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                DateTimePicker::make('answered_at')
                    ->label('Answered At')
                    ->seconds(false)
                    ->nullable(),

                DateTimePicker::make('approved_at')
                    ->label('Approved At')
                    ->seconds(false)
                    ->nullable(),

                DateTimePicker::make('rejected_at')
                    ->label('Rejected At')
                    ->seconds(false)
                    ->nullable(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),

                Hidden::make('ip_address'),

                Hidden::make('user_agent'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.sku')
                    ->label('Product SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('question')
                    ->label('Question')
                    ->limit(55)
                    ->searchable(),

                TextColumn::make('answer')
                    ->label('Answer')
                    ->limit(55)
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),

                TextColumn::make('locale')
                    ->label('Lang')
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('Approved')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('locale')
                    ->options([
                        'ar' => 'Arabic',
                        'he' => 'Hebrew',
                        'en' => 'English',
                    ]),

                SelectFilter::make('product')
                    ->relationship('product', 'sku')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ProductQuestion $record): bool => $record->status !== 'approved')
                    ->action(function (ProductQuestion $record): void {
                        $record->forceFill([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'rejected_at' => null,
                            'is_active' => true,
                        ])->save();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ProductQuestion $record): bool => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(function (ProductQuestion $record): void {
                        $record->forceFill([
                            'status' => 'rejected',
                            'approved_at' => null,
                            'rejected_at' => now(),
                            'is_active' => false,
                        ])->save();
                    }),

                Action::make('quick_answer')
                    ->label('Answer')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->schema([
                        Textarea::make('answer')
                            ->label('Answer')
                            ->required()
                            ->rows(5),
                    ])
                    ->fillForm(fn (ProductQuestion $record): array => [
                        'answer' => $record->answer,
                    ])
                    ->action(function (ProductQuestion $record, array $data): void {
                        $record->forceFill([
                            'answer' => $data['answer'],
                            'answered_by' => auth()->id(),
                            'answered_at' => now(),
                            'status' => 'approved',
                            'approved_at' => $record->approved_at ?? now(),
                            'rejected_at' => null,
                            'is_active' => true,
                        ])->save();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'product',
                'user',
                'answeredBy',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductQuestions::route('/'),
            'create' => Pages\CreateProductQuestion::route('/create'),
            'edit' => Pages\EditProductQuestion::route('/{record}/edit'),
        ];
    }
}
