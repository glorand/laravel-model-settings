<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Managers\AbstractSettingsManager;
use Illuminate\Support\Arr;

trait HasSettings
{
    public function getDefaultSettings(): array
    {
        if (property_exists($this, 'defaultSettings')
            && is_array($this->defaultSettings)) {
            return Arr::wrap($this->defaultSettings);
        } elseif (config('model_settings.defaultSettings.' . $this->getTable())
            && is_array(config('model_settings.defaultSettings.' . $this->getTable()))) {
            return Arr::wrap(config('model_settings.defaultSettings.' . $this->getTable()));
        }
        return [];
    }

    abstract public function getSettingsValue(): array;

    abstract public function settings(): AbstractSettingsManager;
}
