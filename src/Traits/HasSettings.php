<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Managers\AbstractSettingsManager;
use Illuminate\Support\Arr;

trait HasSettings
{
    public function getDefaultSettings(): array
    {
        if (property_exists($this, 'defaultSettings')) {
            return Arr::wrap($this->defaultSettings);
        }

        return [];
    }

    abstract public function getSettingsValue(): array;

    abstract public function settings(): AbstractSettingsManager;
}
