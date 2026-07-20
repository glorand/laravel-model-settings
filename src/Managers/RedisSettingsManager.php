<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Illuminate\Support\Facades\Redis;

/**
 * Class RedisSettingsManager
 * @package Glorand\Model\Settings\Managers
 * @property \Illuminate\Database\Eloquent\Model|\Glorand\Model\Settings\Traits\HasSettings $model
 */
class RedisSettingsManager extends AbstractSettingsManager
{
    /**
     * @return array
     */
    public function getStoredValue(): array
    {
        $this->ensureModelIsPersisted();

        $redisValue = $this->connection()->get($this->model->cacheKey());
        $value = json_decode($redisValue, true);

        return is_array($value) ? $value : [];
    }

    public function apply(array $settings = []): SettingsManagerContract
    {
        $this->ensureModelIsPersisted();
        $this->validate($settings);

        if ([] === $settings) {
            $this->connection()->del($this->model->cacheKey());
        } else {
            $this->connection()->set($this->model->cacheKey(), json_encode($settings));
        }

        return $this;
    }

    /**
     * @return \Illuminate\Redis\Connections\Connection
     */
    private function connection()
    {
        return Redis::connection(config('model_settings.drivers.redis.connection'));
    }
}
