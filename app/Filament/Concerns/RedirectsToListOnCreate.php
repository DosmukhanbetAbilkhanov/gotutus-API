<?php

namespace App\Filament\Concerns;

trait RedirectsToListOnCreate
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
