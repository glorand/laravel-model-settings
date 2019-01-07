<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Managers\TableSettingsManager;
use Glorand\Model\Settings\Models\ModelSettings;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait HasSettingsTable
 * @package Glorand\Model\Settings\Traits
 * @property ModelSettings $modelSettings
 * @property array $settings
 * @method morphOne($model, $name)
 */
trait HasSettingsTable
{
    /**
     * @param string|null $path
     * @param null $default
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function settings(string $path = null, $default = null): SettingsManagerContract
    {
        return $path ? $this->settings()->get($path, $default) : new TableSettingsManager($this);
    }

    protected function getSettingsAttribute()
    {
        if ($this->modelSettings) {
            return $this->modelSettings->settings;
        } else {
            return [];
        }
    }

    protected function modelSettings(): MorphOne
    {
        return $this->morphOne(ModelSettings::class, 'model');
    }
}
