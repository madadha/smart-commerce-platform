<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Product Media';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->disk('public')->directory('products/gallery')->visibility('public')->maxSize(5120)->imageEditor()->requiredWithout('media_file_id'),
            Select::make('role')->options(['main' => 'Featured / Main', 'gallery' => 'Gallery', 'detail' => 'Detail', 'look' => 'Lifestyle', 'banner' => 'Banner', 'package' => 'Package'])->default('gallery')->required(),
            TextInput::make('alt_text.ar')->label('Alt text (Arabic)'),
            TextInput::make('alt_text.he')->label('Alt text (Hebrew)'),
            TextInput::make('alt_text.en')->label('Alt text (English)'),
            Toggle::make('is_active')->default(true),
            TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->disk('public')->square(),
                Tables\Columns\TextColumn::make('role')->badge(),
                Tables\Columns\TextColumn::make('alt_text.ar')->label('Arabic alt')->limit(30),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([CreateAction::make(), BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
