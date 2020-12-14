<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Illuminate\Support\Arr;

trait HasSettings
{
    public function getDefaultSettings(): array
    {
        if (property_exists($this, 'defaultSettings')
            && is_array($this->defaultSettings)) {
            return Arr::wrap($this->defaultSettings);
        } elseif (($defaultSettings = config('model_settings.defaultSettings.' . $this->getTable()))
            && is_array($defaultSettings)) {
            return Arr::wrap($defaultSettings);
        }

        return [];
    }

    abstract public function getSettingsValue(): array;

    abstract public function settings(): SettingsManagerContract;
}
