<?php

namespace App\Filament\Resources\MediaFiles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class MediaFileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('File')
                    ->schema([
                        FileUpload::make('path')
                            ->label('Upload File')
                            ->disk('public')
                            ->directory('media-library')
                            ->visibility('public')
                            ->preserveFilenames(false)
                            ->downloadable()
                            ->openable()
                            ->required()
                            ->maxSize(10240),

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'image' => 'Image',
                                'video' => 'Video',
                                'document' => 'Document',
                                'audio' => 'Audio',
                                'file' => 'File',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->default('image'),

                        TextInput::make('disk')
                            ->label('Disk')
                            ->required()
                            ->default('public')
                            ->maxLength(50),

                        TextInput::make('mime_type')
                            ->label('Mime Type')
                            ->maxLength(255),

                        TextInput::make('size')
                            ->label('Size Bytes')
                            ->numeric(),

                        TextInput::make('width')
                            ->label('Width')
                            ->numeric(),

                        TextInput::make('height')
                            ->label('Height')
                            ->numeric(),

                        TextInput::make('dominant_color')
                            ->label('Dominant Color')
                            ->maxLength(20)
                            ->placeholder('#000000'),

                        Hidden::make('uploaded_by')
                            ->default(fn () => Auth::id()),
                    ])
                    ->columns(2),

                Section::make('Title')
                    ->schema([
                        TextInput::make('title.ar')
                            ->label('Title Arabic')
                            ->maxLength(255),

                        TextInput::make('title.he')
                            ->label('Title Hebrew')
                            ->maxLength(255),

                        TextInput::make('title.en')
                            ->label('Title English')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Alt Text')
                    ->schema([
                        TextInput::make('alt_text.ar')
                            ->label('Alt Arabic')
                            ->maxLength(255),

                        TextInput::make('alt_text.he')
                            ->label('Alt Hebrew')
                            ->maxLength(255),

                        TextInput::make('alt_text.en')
                            ->label('Alt English')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Description')
                    ->schema([
                        Textarea::make('description.ar')
                            ->label('Description Arabic')
                            ->rows(3),

                        Textarea::make('description.he')
                            ->label('Description Hebrew')
                            ->rows(3),

                        Textarea::make('description.en')
                            ->label('Description English')
                            ->rows(3),
                    ])
                    ->columns(3),

                Section::make('AI & Metadata')
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Metadata')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),

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