<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Managers\TableSettingsManager;
use Glorand\Model\Settings\Models\ModelSettings;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Cache;

/**
 * Trait HasSettingsTable
 * @package Glorand\Model\Settings\Traits
 *
 * @property ModelSettings $modelSettings
 * @property array $settings
 * @method morphOne(string $related, string $name, string $type = null, string $id = null, string $localKey = null)
 */
trait HasSettingsTable
{
    use HasSettings;

    /** @var SettingsManagerContract|null */
    private ?SettingsManagerContract $_settingsManager = null;

    /** @var array|null In-memory copy */
    private ?array $_settingsCache = null;

    /* -----------------------------------------------------------------
     |  Boot – register model event listeners
     | -----------------------------------------------------------------
     */
    public static function bootHasSettingsTable(): void
    {
        // When the polymorphic row changes, flush our caches
        static::saved(function ($model) {
            $model->flushSettingsCache();
            Cache::forget($model->getSettingsCacheKey());
        });

        static::deleted(function ($model) {
            $model->flushSettingsCache();
            Cache::forget($model->getSettingsCacheKey());
        });
    }

    /* -----------------------------------------------------------------
     |  Settings Manager
     | -----------------------------------------------------------------
     */
    public function settings(): SettingsManagerContract
    {
        return $this->_settingsManager ??= new TableSettingsManager($this);
    }

    /* -----------------------------------------------------------------
     |  Settings Value (lazy + in-memory)
     | -----------------------------------------------------------------
     */
    public function getSettingsValue(): array
    {
        if ($this->_settingsCache !== null) {
            return $this->_settingsCache;
        }

        if (! config('model_settings.settings_table_use_cache')) {
            return $this->_settingsCache = $this->loadSettingsFromDatabase();
        }

        return $this->_settingsCache = Cache::rememberForever(
            $this->getSettingsCacheKey(),
            fn () => $this->loadSettingsFromDatabase()
        );
    }

    /**
     * Reload settings and refresh all caches.
     */
    public function refreshSettings(): array
    {
        $this->flushSettingsCache();
        Cache::forget($this->getSettingsCacheKey());

        return $this->getSettingsValue();
    }

    /**
     * Flush only the in-RAM copy.
     */
    public function flushSettingsCache(): void
    {
        $this->_settingsCache = null;
    }

    /**
     * Raw DB hit (no caching layer).
     */
    private function loadSettingsFromDatabase(): array
    {
        /** @var ModelSettings|null $row */
        $row = $this->modelSettings()->first(['settings']);

        return $row?->settings ?? [];
    }

    /* -----------------------------------------------------------------
     |  Relations
     | -----------------------------------------------------------------
     */
    public function modelSettings(): MorphOne
    {
        return $this->morphOne(ModelSettings::class, 'model');
    }


    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */
    public function getSettingsCacheKey(): string
    {
        return config('model_settings.settings_table_cache_prefix')
            . $this->getTable() . '::' . $this->getKey();
    }

    /* -----------------------------------------------------------------
     |  Abstract requirements
     | -----------------------------------------------------------------
     */
    abstract public function getTable();
}