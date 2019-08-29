<?php

namespace Glorand\Model\Settings\Traits;

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
     * @return array
     */
    abstract public function getSettingsValue(): array;
}
