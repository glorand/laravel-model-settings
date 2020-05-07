<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Managers\TableSettingsManager;
use Glorand\Model\Settings\Models\ModelSettings;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Cache;

/**
 * Trait HasSettingsTable
 * @package Glorand\Model\Settings\Traits
 * @property ModelSettings $modelSettings
 * @property array $settings
 * @method morphOne($model, $name)
 */
trait HasSettingsTable
{
    use HasSettings;

    /**
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function settings(): SettingsManagerContract
    {
        return new TableSettingsManager($this);
    }

    /**
     * @return array
     */
    public function getSettingsValue(): array
    {
        if (config('model_settings.settings_table_use_cache')) {
            return Cache::rememberForever($this->getSettingsCacheKey(), function () {
                return $this->__getSettingsValue();
            });
        }

        return $this->__getSettingsValue();
    }

    private function __getSettingsValue(): array
    {
        if ($modelSettings = $this->modelSettings()->first()) {
            return $modelSettings->settings;
        }

        return [];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function modelSettings(): MorphOne
    {
        return $this->morphOne(ModelSettings::class, 'model');
    }

    public function getSettingsCacheKey(): string
    {
        return config('model_settings.settings_table_cache_prefix') . $this->getTable() . '::' . $this->getKey();
    }

    abstract public function getTable();
}
