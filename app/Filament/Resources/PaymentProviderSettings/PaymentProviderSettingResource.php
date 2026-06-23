<?php

namespace App\Filament\Resources\PaymentProviderSettings;

use App\Filament\Resources\PaymentProviderSettings\Pages\EditPaymentProviderSetting;
use App\Filament\Resources\PaymentProviderSettings\Pages\ListPaymentProviderSettings;
use App\Models\PaymentProviderSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentProviderSettingResource extends \App\Filament\Resources\BaseResource
{
    protected static ?string $model = PaymentProviderSetting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Payment Providers';

    protected static ?string $modelLabel = 'Payment Provider';

    protected static ?string $pluralModelLabel = 'Payment Providers';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Provider Status')
                ->description('Only verified providers with complete credentials appear at checkout.')
                ->schema([
                    Select::make('provider')
                        ->options([
                            'payplus' => 'PayPlus',
                            'paypal' => 'PayPal',
                            'stripe' => 'Stripe',
                            'paddle' => 'Paddle',
                        ])
                        ->disabled()
                        ->dehydrated(),
                    Select::make('mode')
                        ->options([
                            'sandbox' => 'Sandbox / Test',
                            'live' => 'Live / Production',
                        ])
                        ->required(),
                    Toggle::make('is_enabled')
                        ->label('Enabled at Checkout')
                        ->helperText('The provider remains hidden until its connection is verified.'),
                    Select::make('connection_status')
                        ->options([
                            'not_configured' => 'Not Configured',
                            'untested' => 'Configured — Not Tested',
                            'verified' => 'Verified',
                            'failed' => 'Connection Failed',
                        ])
                        ->disabled()
                        ->dehydrated(),
                    TextInput::make('sort_order')->numeric()->default(0),
                    TagsInput::make('supported_currencies')
                        ->placeholder('ILS, USD, EUR')
                        ->columnSpanFull(),
                ])
                ->columns(3),

            Section::make('Checkout Content')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('display_name.ar')->label('Name AR')->required(),
                        TextInput::make('display_name.he')->label('Name HE'),
                        TextInput::make('display_name.en')->label('Name EN')->required(),
                    ]),
                    Grid::make(3)->schema([
                        Textarea::make('description.ar')->label('Description AR')->rows(2),
                        Textarea::make('description.he')->label('Description HE')->rows(2),
                        Textarea::make('description.en')->label('Description EN')->rows(2),
                    ]),
                ]),

            Section::make('Sandbox Credentials')
                ->description('Encrypted test credentials. Use these until the full payment flow passes sandbox acceptance tests.')
                ->schema(self::credentialFields('sandbox_credentials'))
                ->columns(2)
                ->collapsible(),

            Section::make('Live Credentials')
                ->description('Encrypted production credentials. Do not enable Live mode before webhook verification succeeds.')
                ->schema(self::credentialFields('live_credentials'))
                ->columns(2)
                ->collapsed()
                ->collapsible(),

            Section::make('Connection Diagnostics')
                ->schema([
                    TextInput::make('last_tested_at')
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('last_error')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsed()
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('display_name.en')
                    ->label('Provider')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mode')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'live' ? 'danger' : 'info'),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('connection_status')
                    ->label('Connection')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'failed' => 'danger',
                        'untested' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('last_tested_at')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('Never'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentProviderSettings::route('/'),
            'edit' => EditPaymentProviderSetting::route('/{record}/edit'),
        ];
    }

    private static function credentialFields(string $prefix): array
    {
        return [
            TextInput::make("{$prefix}.api_key")
                ->label('API Key')
                ->password()
                ->revealable()
                ->visible(fn (?PaymentProviderSetting $record): bool => in_array($record?->provider, ['payplus', 'paddle'], true)),
            TextInput::make("{$prefix}.secret_key")
                ->label('Secret Key')
                ->password()
                ->revealable()
                ->visible(fn (?PaymentProviderSetting $record): bool => in_array($record?->provider, ['payplus', 'stripe'], true)),
            TextInput::make("{$prefix}.payment_page_uid")
                ->label('Payment Page UID')
                ->password()
                ->revealable()
                ->visible(fn (?PaymentProviderSetting $record): bool => $record?->provider === 'payplus'),
            TextInput::make("{$prefix}.terminal_uid")
                ->label('Terminal UID')
                ->password()
                ->revealable()
                ->visible(fn (?PaymentProviderSetting $record): bool => $record?->provider === 'payplus'),
            TextInput::make("{$prefix}.client_id")
                ->label('Client ID')
                ->password()
                ->revealable()
                ->visible(fn (?PaymentProviderSetting $record): bool => $record?->provider === 'paypal'),
            TextInput::make("{$prefix}.client_secret")
                ->label('Client Secret')
                ->password()
                ->revealable()
                ->visible(fn (?PaymentProviderSetting $record): bool => $record?->provider === 'paypal'),
            TextInput::make("{$prefix}.webhook_id")
                ->label('Webhook ID')
                ->password()
                ->revealable()
                ->visible(fn (?PaymentProviderSetting $record): bool => $record?->provider === 'paypal'),
            TextInput::make("{$prefix}.publishable_key")
                ->label('Publishable Key')
                ->password()
                ->revealable()
                ->visible(fn (?PaymentProviderSetting $record): bool => $record?->provider === 'stripe'),
            TextInput::make("{$prefix}.client_token")
                ->label('Client-side Token')
                ->password()
                ->revealable()
                ->visible(fn (?PaymentProviderSetting $record): bool => $record?->provider === 'paddle'),
            TextInput::make("{$prefix}.webhook_secret")
                ->label('Webhook Secret')
                ->password()
                ->revealable()
                ->visible(fn (?PaymentProviderSetting $record): bool => in_array($record?->provider, ['stripe', 'paddle'], true)),
        ];
    }
}
