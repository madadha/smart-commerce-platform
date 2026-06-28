<?php

namespace App\Filament\Resources\Brands;

use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Filament\Resources\Brands\Pages\EditBrand;
use App\Filament\Resources\Brands\Pages\ListBrands;
use App\Models\Brand;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BrandResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = Brand::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Brands';

    protected static ?string $modelLabel = 'Brand';

    protected static ?string $pluralModelLabel = 'Brands';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Brand Information')
                    ->schema([
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

                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('brands/logos')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor(),

                        FileUpload::make('banner_image')
                            ->label('Banner Image')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('brands/banners')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor(),

                        Textarea::make('description.ar')
                            ->label('Description Arabic')
                            ->rows(3),

                        Textarea::make('description.he')
                            ->label('Description Hebrew')
                            ->rows(3),

                        Textarea::make('description.en')
                            ->label('Description English')
                            ->rows(3),

                        TextInput::make('website_url')
                            ->label('Website URL')
                            ->url()
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->formatStateUsing(fn ($state, Brand $record): string => $record->getName('ar'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('website_url')
                    ->label('Website')
                    ->limit(30),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'edit' => EditBrand::route('/{record}/edit'),
        ];
    }
}
