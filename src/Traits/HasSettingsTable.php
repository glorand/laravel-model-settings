<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Managers\TableSettingsManager;
use Glorand\Model\Settings\Models\ModelSettings;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Cache;

trait HasSettingsTable
{
    use HasSettings;

    protected $settingsRuntimeCache = null;
    protected $settingsManagerInstance = null;
    protected static $configCache = [];

    /**
     * Returns a settings manager instance for the model, creating one if necessary.
     * @return SettingsManagerContract
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function settings(): SettingsManagerContract
    {
        if ($this->settingsManagerInstance === null) {
            $this->settingsManagerInstance = new TableSettingsManager($this);
        }

        return $this->settingsManagerInstance;
    }

    /**
     * Retrieves the settings value for the model, optionally using a cache.
     * @return array
     */
    public function getSettingsValue(): array
    {
        // Runtime cache = ZERO Redis calls after first access
        if ($this->settingsRuntimeCache !== null) {
            return $this->settingsRuntimeCache;
        }

        // Cache config lookup once per class (not per instance)
        if (!isset(static::$configCache['use_cache'])) {
            static::$configCache['use_cache'] = config('model_settings.settings_table_use_cache');
        }

        if (static::$configCache['use_cache']) {
            // Only 1 Redis call per request
            $this->settingsRuntimeCache = Cache::rememberForever(
                $this->getSettingsCacheKey(),
                fn() => $this->__getSettingsValue()
            );
        } else {
            $this->settingsRuntimeCache = $this->__getSettingsValue();
        }

        return $this->settingsRuntimeCache;
    }

    /**
     * Retrieves the settings value for the model.
     * @return array
     */
    private function __getSettingsValue(): array
    {
        // Eager loading support (prevents N+1)
        if ($this->relationLoaded('modelSettings')) {
            $modelSettings = $this->getRelation('modelSettings');
            return $modelSettings ? $modelSettings->settings : [];
        }

        $modelSettings = $this->modelSettings()
            ->select('settings', 'model_id', 'model_type')
            ->first();

        return $modelSettings ? $modelSettings->settings : [];
    }

    /**
     * Define an inverse one-to-one or many relationship.
     * @return MorphOne
     */
    public function modelSettings(): MorphOne
    {
        return $this->morphOne(ModelSettings::class, 'model');
    }

    /**
     * Retrieves the cache key for settings associated with the model.
     *
     * This method ensures that a consistent cache key is generated for
     * the model's settings by utilizing a shared static configuration
     * for the cache prefix and combining it with the table name and
     * primary key of the current model instance.
     *
     * @return string The generated cache key for the model's settings.
     */
    public function getSettingsCacheKey(): string
    {
        // Static config cache shared across all instances
        if (!isset(static::$configCache['cache_prefix'])) {
            static::$configCache['cache_prefix'] = config('model_settings.settings_table_cache_prefix');
        }

        return static::$configCache['cache_prefix'] . $this->getTable() . '::' . $this->getKey();
    }

    /**
     * Clears the runtime cache and optionally the cache for the model's settings.'
     * @return void
     */
    public function clearSettingsCache(): void
    {
        $this->settingsRuntimeCache = null;

        if (static::$configCache['use_cache'] ?? config('model_settings.settings_table_use_cache')) {
            Cache::forget($this->getSettingsCacheKey());
        }
    }

    /**
     * Retrieves the name of the database table associated with the model.
     * @return string
     */
    abstract public function getTable();
}
