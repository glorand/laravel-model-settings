<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Models\ModelSettings;
use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property Model|HasSettings $model
 */
class TableSettingsManager extends AbstractSettingsManager
{
    /**
     * @throws ModelSettingsException
     */
    public function getStoredValue(): array
    {
        $this->ensureModelIsPersisted();

        if (config('model_settings.drivers.table.use_cache', true)) {
            return Cache::rememberForever($this->model->getSettingsCacheKey(), function () {
                return $this->fetchStoredValue();
            });
        }

        return $this->fetchStoredValue();
    }

    /**
     * @param array $settings
     * @return SettingsManagerContract
     * @throws ModelSettingsException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function apply(array $settings = []): SettingsManagerContract
    {
        $this->ensureModelIsPersisted();
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

    private function fetchStoredValue(): array
    {
        if ($modelSettings = $this->model->modelSettings()->first()) {
            return $modelSettings->settings;
        }

        return [];
    }
}
