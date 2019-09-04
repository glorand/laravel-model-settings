<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;

/**
 * Class FieldSettingsManager
 * @package Glorand\Model\Settings\Managers
 * @property \Illuminate\Database\Eloquent\Model|\Glorand\Model\Settings\Traits\HasSettingsRedis $model
 */
class RedisSettingsManager extends AbstractSettingsManager
{
    public function apply(array $settings = []): SettingsManagerContract
    {
        Redis::set($this->model->cacheKey(), json_encode($settings));

        return $this;
    }

    public function set(string $path, $value): SettingsManagerContract
    {
        $settings = $this->all();
        Arr::set($settings, $path, $value);

        return $this->apply($settings);
    }

    /**
     * Delete an item by its unique path.
     *
     * @param string|null $path
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function delete(string $path = null): SettingsManagerContract
    {
        {
            if (!$path) {
                $settings = [];
            } else {
                $settings = $this->all();
                Arr::forget($settings, $path);
            }

            $this->apply($settings);

            return $this;
        }
    }
}
