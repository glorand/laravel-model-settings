<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

/**
 * @property Model|HasSettings $model
 */
class RedisSettingsManager extends AbstractSettingsManager
{
    /**
     * @throws ModelSettingsException
     */
    public function getStoredValue(): array
    {
        $this->ensureModelIsPersisted();

        $redisValue = $this->connection()->get($this->model->cacheKey());
        $value = json_decode($redisValue, true);

        return is_array($value) ? $value : [];
    }

    /**
     * @throws ModelSettingsException
     */
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

    private function connection(): Connection
    {
        return Redis::connection(config('model_settings.drivers.redis.connection'));
    }
}
