<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Models\ModelSettings;

/**
 * Class TableSettingsManager
 * @package Glorand\Model\Settings\Managers
 * @property  \Illuminate\Database\Eloquent\Model|\Glorand\Model\Settings\Traits\HasSettingsTable $model
 */
class TableSettingsManager extends AbstractSettingsManager
{
    /**
     * @param array $settings
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     * @throws \Exception
     */
    public function apply(array $settings = []): SettingsManagerContract
    {
        if (!$modelSettings = $this->model->modelSettings()->first()) {
            $modelSettings = new ModelSettings();
            $modelSettings->model()->associate($this->model);
        }
        $modelSettings->settings = $settings;
        $modelSettings->save();

        cache()->forget($this->model->getSettingsCacheKey());

        return $this;
    }
}
