<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Managers\RedisSettingsManager;
use Illuminate\Support\Facades\Redis;

/**
 * Trait HasSettingsRedis.
 *
 * @property array $settings
 */
trait HasSettingsRedis
{
    use HasSettings;

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     *
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function settings(): SettingsManagerContract
    {
        return new RedisSettingsManager($this);
    }

    public function getSettingsValue(): array
    {
        $redisValue = Redis::connection()->get($this->cacheKey());
        $value = json_decode($redisValue, true);

        return is_array($value) ? $value : [];
    }

    public function cacheKey(string $key = null): string
    {
        return sprintf(
            'r-k-%s:%s',
            $this->getTable(),
            $this->getKey()
        ).$key;
    }

    abstract public function getTable();

    abstract public function getKey();
}
