<?php

namespace App\Filament\Resources\Languages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LanguageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('native_name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('direction')
                    ->required()
                    ->default('ltr'),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_default')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
