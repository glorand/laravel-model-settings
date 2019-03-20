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
        if ($modelSettings = $this->modelSettings()->first()) {
            return $modelSettings->settings;
        } else {
            return [];
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function modelSettings(): MorphOne
    {
        return $this->morphOne(ModelSettings::class, 'model');
    }
}
