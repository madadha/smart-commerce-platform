<?php

namespace App\Filament\Resources\ProductMedia\Schemas;

use App\Models\MediaFile;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductMediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Media Information')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(fn (): array => Product::query()
                                ->orderBy('sort_order')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (Product $product) => [
                                    $product->id => $product->getName('ar') . ' - ' . $product->slug,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('role')
                            ->label('Image Role')
                            ->options([
                                'main' => 'Main Image',
                                'gallery' => 'Gallery Image',
                                'detail' => 'Detail Image',
                                'look' => 'Look Image',
                                'banner' => 'Banner Image',
                                'package' => 'Package Image',
                            ])
                            ->required()
                            ->default('gallery'),

                        FileUpload::make('image')
                            ->label('Upload Image')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('products/gallery')
                            ->visibility('public')
                            ->imageEditor()
                            ->downloadable()
                            ->openable()
                            ->maxSize(5120)
                            ->helperText('Upload image directly for this product.'),

                        Select::make('media_file_id')
                            ->label('Or Select From Media Library')
                            ->options(fn (): array => MediaFile::query()
                                ->where('type', 'image')
                                ->latest()
                                ->get()
                                ->mapWithKeys(fn (MediaFile $mediaFile) => [
                                    $mediaFile->id => $mediaFile->getTitle('ar') . ' - ' . $mediaFile->path,
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->helperText('Optional: choose an existing image from Media Library.'),

                        TextInput::make('alt_text.ar')
                            ->label('Alt Text Arabic')
                            ->maxLength(255),

                        TextInput::make('alt_text.he')
                            ->label('Alt Text Hebrew')
                            ->maxLength(255),

                        TextInput::make('alt_text.en')
                            ->label('Alt Text English')
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}
