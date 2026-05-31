<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $modelLabel = 'Category';

    protected static ?string $pluralModelLabel = 'Categories';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    public static function form(Schema $schema): Schema
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
                            ->directory('categories/images')
                            ->imageEditor(),

                        FileUpload::make('icon')
                            ->label('Icon')
                            ->image()
                            ->directory('categories/icons')
                            ->imageEditor(),

                        FileUpload::make('banner_image')
                            ->label('Banner Image')
                            ->image()
                            ->directory('categories/banners')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->formatStateUsing(fn ($state, Category $record): string => $record->getFullPath('ar'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.slug')
                    ->label('Parent')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('show_in_menu')
                    ->label('Menu')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
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
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}