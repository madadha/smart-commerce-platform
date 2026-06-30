<?php

namespace App\Filament\Resources\GameRegions\Schemas;

use App\Support\Localization\ActiveLanguageRegistry;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GameRegionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Region Information')
                ->schema([
                    Grid::make(3)->schema(self::localizedTextInputs('name', 'Name', required: true)),

                    TextInput::make('code')
                        ->label('Code')
                        ->required()
                        ->maxLength(80)
                        ->unique(ignoreRecord: true)
                        ->helperText('Examples: GLOBAL, MIDDLE_EAST, EUROPE. Stored uppercase.'),

                    FileUpload::make('icon')
                        ->label('Icon')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                        ->disk('public')
                        ->directory('games/regions')
                        ->visibility('public')
                        ->maxSize(2048)
                        ->imageEditor(),

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
}
