<?php

namespace App\Filament\Resources\ProductReviews\Schemas;

use App\Models\Product;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Review Information')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(fn (): array => Product::query()
                                ->orderBy('id', 'desc')
                                ->get()
                                ->mapWithKeys(fn (Product $product) => [
                                    $product->id => method_exists($product, 'getName')
                                        ? $product->getName('ar')
                                        : ($product->name ?? $product->slug ?? ('Product #' . $product->id)),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('user_id')
                            ->label('User')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $user) => [
                                    $user->id => $user->name . ' - ' . $user->email,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload(),

                        TextInput::make('reviewer_name')
                            ->label('Reviewer Name')
                            ->required()
                            ->maxLength(120),

                        TextInput::make('reviewer_email')
                            ->label('Reviewer Email')
                            ->email()
                            ->maxLength(180),

                        Select::make('rating')
                            ->label('Rating')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ])
                            ->required()
                            ->default(5),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending'),

                        TextInput::make('locale')
                            ->label('Locale')
                            ->maxLength(5)
                            ->default('ar'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Section::make('Comment')
                    ->schema([
                        Textarea::make('comment')
                            ->label('Comment')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}