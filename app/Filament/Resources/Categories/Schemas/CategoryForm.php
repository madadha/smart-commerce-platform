<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Information')
                    ->schema([
                        Select::make('parent_id')
                            ->label('Parent Category')
                            ->options(function (?Category $record): array {
                                return Category::query()
                                    ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                    ->orderBy('sort_order')
                                    ->orderBy('id')
                                    ->get()
                                    ->mapWithKeys(fn (Category $category) => [
                                        $category->id => $category->getFullPath('ar'),
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        TextInput::make('name.ar')
                            ->label('Name Arabic')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name.he')
                            ->label('Name Hebrew')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name.en')
                            ->label('Name English')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Textarea::make('description.ar')
                            ->label('Description Arabic')
                            ->rows(3),

                        Textarea::make('description.he')
                            ->label('Description Hebrew')
                            ->rows(3),

                        Textarea::make('description.en')
                            ->label('Description English')
                            ->rows(3),

                        FileUpload::make('image')
                            ->label('Image')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('categories/images')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor(),

                        FileUpload::make('icon')
                            ->label('Icon')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('categories/icons')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor(),

                        FileUpload::make('banner_image')
                            ->label('Banner Image')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('categories/banners')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Toggle::make('show_in_menu')
                            ->label('Show In Menu')
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Section::make('SEO')
                    ->schema([
                        TextInput::make('seo_title.ar')
                            ->label('SEO Title Arabic')
                            ->maxLength(255),

                        TextInput::make('seo_title.he')
                            ->label('SEO Title Hebrew')
                            ->maxLength(255),

                        TextInput::make('seo_title.en')
                            ->label('SEO Title English')
                            ->maxLength(255),

                        Textarea::make('seo_description.ar')
                            ->label('SEO Description Arabic')
                            ->rows(3),

                        Textarea::make('seo_description.he')
                            ->label('SEO Description Hebrew')
                            ->rows(3),

                        Textarea::make('seo_description.en')
                            ->label('SEO Description English')
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }
}
