<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Models\ModelSettings;
use Illuminate\Support\Facades\Redis;

/**
 * Class TableSettingsManager
 * @package Glorand\Model\Settings\Managers
 * @property  \Illuminate\Database\Eloquent\Model|\Glorand\Model\Settings\Traits\HasSettingsTable $model
 */
class TableSettingsManager extends AbstractSettingsManager
{
    /**
     * @param  array  $settings
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     * @throws \Exception
     */
    public function apply(array $settings = []): SettingsManagerContract
    {
        $this->validate($settings);

        $modelSettings = $this->model->modelSettings()->first();

        if (! count($settings)) {
            if ($modelSettings) {
                $modelSettings->delete();               // fires deleted event → cache flushed
            }
        } else {
            if (! $modelSettings) {
                $modelSettings = new ModelSettings();
                $modelSettings->setConnection($this->model->getConnectionName() ?? config('database.default'));
                $modelSettings->model()->associate($this->model);
            }

            $modelSettings->settings = $settings;
            $modelSettings->save();                     // fires saved event → cache flushed
        }

        // Optional: warm Redis cache instantly via pipeline
        $this->warmCache($settings);

        $this->model->flushSettingsCache();

        cache()->forget($this->model->getSettingsCacheKey());

        return $this;
    }

    /* -----------------------------------------------------------------
     |  Internal helpers
     | -----------------------------------------------------------------
     */
    private function warmCache(array $settings): void
    {
        $cacheKey = $this->model->getSettingsCacheKey();
        $payload  = $this->compress(json_encode($settings));

        Redis::connection()->pipeline(function ($pipe) use ($cacheKey, $payload, $settings) {
            $pipe->del($cacheKey);
            if (count($settings)) {
                $pipe->set($cacheKey, $payload);
            }
        });
    }

    private function compress(string $data): string
    {
        return gzencode($data, 6);
    }
}