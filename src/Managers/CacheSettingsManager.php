<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * @property Model|HasSettings $model
 */
class CacheSettingsManager extends AbstractSettingsManager
{
    /**
     * @throws ModelSettingsException
     * @throws InvalidArgumentException
     */
    public function getStoredValue(): array
    {
        $this->ensureModelIsPersisted();

        $cacheValue = $this->cache()->get($this->model->cacheKey());

        return $this->decodeJsonValue($cacheValue);
    }

    /**
     * @throws ModelSettingsException
     */
    public function apply(array $settings = []): SettingsManagerContract
    {
        $this->ensureModelIsPersisted();
        $this->validate($settings);

        $key = $this->model->cacheKey();
        if ([] === $settings) {
            $this->cache()->forget($key);

            return $this;
        }

        $ttl = $this->getCacheTtl();
        $value = json_encode($settings);

        if (null === $ttl) {
            $this->cache()->forever($key, $value);
        } else {
            $this->cache()->put($key, $value, $ttl);
        }

        return $this;
    }

    private function cache(): Repository
    {
        return Cache::store(config('model_settings.drivers.cache.store') ?: null);
    }

    /**
     * Configured lifetime in seconds, or null to store forever.
     *
     * @throws ModelSettingsException
     */
    private function getCacheTtl(): ?int
    {
        $minutes = config('model_settings.drivers.cache.ttl');
        if (null === $minutes || '' === $minutes) {
            return null;
        }

        if (!is_numeric($minutes) || $minutes <= 0) {
            throw new ModelSettingsException(
                sprintf(
                    'Invalid [model_settings.drivers.cache.ttl] value [%s]: expected a positive number of minutes,'
                    . ' or null to store the settings forever.',
                    is_scalar($minutes) ? $minutes : gettype($minutes)
                )
            );
        }

        return (int) ($minutes * 60);
    }
}
