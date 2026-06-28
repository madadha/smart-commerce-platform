<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Support\Str;
use UnitEnum;

abstract class BaseResource extends Resource
{
    protected static function translateAdminLabel(?string $label): string
    {
        if (blank($label)) {
            return '';
        }

        $headline = Str::headline($label);
        $translatedHeadline = __($headline);

        if (is_string($translatedHeadline) && $translatedHeadline !== $headline) {
            return $translatedHeadline;
        }

        $translated = __($label);

        return is_string($translated) && $translated !== $label ? $translated : $label;
    }

    public static function getNavigationLabel(): string
    {
        return static::translateAdminLabel(parent::getNavigationLabel());
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        $group = parent::getNavigationGroup();

        return is_string($group) ? static::translateAdminLabel($group) : $group;
    }

    public static function getModelLabel(): string
    {
        $modelClass = static::getModel();
        $label = static::$modelLabel ?? ($modelClass ? Str::headline(class_basename($modelClass)) : parent::getModelLabel());

        return static::translateAdminLabel($label);
    }

    public static function getPluralModelLabel(): string
    {
        $modelClass = static::getModel();
        $label = static::$pluralModelLabel ?? ($modelClass ? Str::headline(Str::plural(class_basename($modelClass))) : parent::getPluralModelLabel());

        return static::translateAdminLabel($label);
    }
}
