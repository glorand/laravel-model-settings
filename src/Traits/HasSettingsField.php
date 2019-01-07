<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Managers\FieldSettingsManager;

/**
 * Trait HasSettingsField
 * @package Glorand\Model\Settings\Traits
 * @property array $settings
 */
trait HasSettingsField
{
    /**
     * @param string $path
     * @param null $default
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function settings(string $path = null, $default = null): SettingsManagerContract
    {
        return $path ? $this->settings()->get($path, $default) : new FieldSettingsManager($this);
    }

    protected function getSettingsAttribute()
    {
        return json_decode($this->getAttributes()['settings'], true);
    }

    public function setSettingsAttribute($settings)
    {
        $this->attributes['settings'] = json_encode($settings);
    }
}
