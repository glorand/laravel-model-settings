<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Illuminate\Support\Arr;

/**
 * @property array $settingRules
 * @property array $defaultSettings
 */
trait HasSettings
{
    public function getRules(): array
    {
        if (property_exists($this, 'settingsRules') && is_array($this->settingsRules)) {
            return $this->settingsRules;
        }

        return [];
    }

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

    public function __call($name, $args)
    {
        if (isset($this->invokeSettingsBy) && $name === $this->invokeSettingsBy) {
            return $this->settings();
        }

        return is_callable(['parent', '__call']) ? parent::__call($name, $args) : null;
    }

    abstract public function getSettingsValue(): array;

    abstract public function settings(): SettingsManagerContract;
}
