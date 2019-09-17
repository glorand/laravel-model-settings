<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Managers\RedisSettingsManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;

/**
 * Trait HasSettingsRedis
 * @package Glorand\Model\Settings\Traits
 * @property array $settings
 */
trait HasSettingsRedis
{
    use HasSettings;

    /**
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function settings(): SettingsManagerContract
    {
        return new RedisSettingsManager($this);
    }

    public function getSettingsValue(): array
    {
        $redisValue = Redis::get($this->cacheKey());

        return Arr::wrap(json_decode($redisValue, true));
    }

    public function cacheKey(string $key = null): string
    {
        return sprintf(
                "r-k-%s:%s:%s",
                $this->getTable(),
                $this->getKey(),
                $this->updated_at->timestamp
            ) . $key;
    }

    abstract public function getTable();

    abstract public function getKey();
}
