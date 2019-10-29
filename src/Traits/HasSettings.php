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

    /**
     * Accessor to set default settings when one is
     * set in model property
     *
     * @param $value
     * @return array
     */
    public function getSettingsAttribute($value)
    {
        if (is_array($this->defaultSettings) && $this->defaultSettings) {
            return $value ? array_merge(json_decode($value, true), $this->defaultSettings) : $this->defaultSettings;
        }

        return $value;
    }

    abstract public function getSettingsValue(): array;

    abstract public function settings(): AbstractSettingsManager;
}
