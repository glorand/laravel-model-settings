<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Models\ModelSettings;
use Illuminate\Support\Facades\Cache;

/**
 * Class TableSettingsManager
 * @package Glorand\Model\Settings\Managers
 * @property  \Illuminate\Database\Eloquent\Model|\Glorand\Model\Settings\Traits\HasSettings $model
 */
class TableSettingsManager extends AbstractSettingsManager
{
    /**
     * @return array
     */
    public function getStoredValue(): array
    {
        if (config('model_settings.drivers.table.use_cache', true)) {
            return Cache::rememberForever($this->model->getSettingsCacheKey(), function () {
                return $this->fetchStoredValue();
            });
        }

        return $this->fetchStoredValue();
    }

    /**
     * @param  array  $settings
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function apply(array $settings = []): SettingsManagerContract
    {
        $this->validate($settings);

        $modelSettings = $this->model->modelSettings()->first();
        if (!count($settings)) {
            if ($modelSettings) {
                $modelSettings->delete();
            }
        } else {
            if (!$modelSettings) {
                $modelSettings = new ModelSettings();
                $modelSettings->setConnection($this->model->getConnectionName() ?? config('database.default'));
                $modelSettings->model()->associate($this->model);
            }
            $modelSettings->settings = $settings;
            $modelSettings->save();
        }

        cache()->forget($this->model->getSettingsCacheKey());

        return $this;
    }

    /**
     * @return array
     */
    private function fetchStoredValue(): array
    {
        if ($modelSettings = $this->model->modelSettings()->first()) {
            return $modelSettings->settings;
        }

        return [];
    }
}
