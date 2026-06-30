<?php

namespace App\Filament\Resources\Games\Schemas;

use App\Models\GameRegion;
use App\Support\Localization\ActiveLanguageRegistry;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Game Information')
                ->schema([
                    Grid::make(3)->schema(self::localizedTextInputs('name', 'Name', required: true)),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->helperText('Leave empty to auto-generate from the game name.'),

                    Grid::make(3)->schema(self::localizedTextareas('description', 'Description')),

                    Grid::make(2)->schema([
                        FileUpload::make('icon')
                            ->label('Icon')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                            ->disk('public')
                            ->directory('games/icons')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->imageEditor(),

                        FileUpload::make('banner_image')
                            ->label('Banner Image')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->disk('public')
                            ->directory('games/banners')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor(),
                    ]),
                ]),

            Section::make('Top-Up Settings')
                ->schema([
                    TextInput::make('default_provider')
                        ->label('Default Provider')
                        ->placeholder('Manual Team / Midasbuy / Provider name')
                        ->maxLength(255),

                    Select::make('regions')
                        ->label('Supported Regions')
                        ->relationship('regions', 'code')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->getOptionLabelFromRecordUsing(fn (GameRegion $record): string => self::regionOptionLabel($record)),

                    Toggle::make('supports_player_validation')
                        ->label('Supports Player Validation')
                        ->default(false)
                        ->helperText('Enable only when an official/provider validation endpoint exists.'),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    TextInput::make('sort_order')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(0)
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    private static function localizedTextInputs(string $field, string $label, bool $required = false): array
    {
        return collect(ActiveLanguageRegistry::SUPPORTED_CODES)
            ->map(fn (string $locale) => TextInput::make("{$field}.{$locale}")
                ->label($label.' '.strtoupper($locale))
                ->required($required && app(ActiveLanguageRegistry::class)->isActive($locale))
                ->visible(fn (): bool => app(ActiveLanguageRegistry::class)->isActive($locale))
                ->maxLength(255))
            ->all();
    }

    private static function localizedTextareas(string $field, string $label): array
    {
        return collect(ActiveLanguageRegistry::SUPPORTED_CODES)
            ->map(fn (string $locale) => Textarea::make("{$field}.{$locale}")
                ->label($label.' '.strtoupper($locale))
                ->visible(fn (): bool => app(ActiveLanguageRegistry::class)->isActive($locale))
                ->rows(3))
            ->all();
    }

    private static function regionOptionLabel(GameRegion $record): string
    {
        $locale = app(ActiveLanguageRegistry::class)->defaultCode();
        $name = $record->getName($locale);

        if (str_contains($name, '????') || $name === 'Region') {
            $name = $record->getName('en');
        }

        if (str_contains($name, '????') || $name === 'Region') {
            $name = $record->code;
        }

        return trim($name.' - '.$record->code);
    }
}
